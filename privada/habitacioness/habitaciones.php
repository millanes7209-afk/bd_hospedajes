<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Ajustar orden según preferencia del usuario
$orderBy = (isset($_GET['orden']) && $_GET['orden'] == 'tipo') ? "thab.nombre, hab.numero ASC" : "hab.numero ASC";

// Consulta SQL actualizada
$sql = "SELECT  thab.tipohabitacionID, hab.habitacionID, hab.bano, hab.tv, hab.ventilador, 
                thab.nombre, thab.precio, hab.estado as estado, hab.numero as numero, 
                hab.descripcion as descripcion,
                (SELECT GROUP_CONCAT(CONCAT('- ', c.nombres, ' ', c.apellido1) SEPARATOR '<br>')
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X') AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo,
                (SELECT h.monto 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS precio_pactado,
                (SELECT h.hospedajeID
                 FROM hospedajes h
                 WHERE h.habitacionID = hab.habitacionID
                 AND h.empresaID = ?
                 AND h.estado = 'ACTIVO'
                 AND h._estado <> 'X'
                 LIMIT 1) AS hospedaje_activo_id
        FROM    habitaciones hab
        JOIN    tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        WHERE   thab._estado <> 'X'
        AND     hab._estado <> 'X'
        AND     hab.empresaID = ?
        ORDER BY $orderBy";

$rs = $db->obtenerTodo($sql, array($empresaID, $empresaID, $empresaID, $empresaID, $empresaID));


// Guardar en sesión para ver después de redirección
$_SESSION['debug_rs_count'] = is_array($rs) ? count($rs) : 'NO ES ARRAY';
$_SESSION['debug_rs_empty'] = empty($rs) ? 'VACÍO' : 'CON DATOS';
if (is_array($rs) && !empty($rs)) {
    $_SESSION['debug_primer_registro'] = print_r($rs[0], true);
}

// Verificar si hay una caja abierta para el usuario actual
$usuarioID = $_SESSION["sesion_id_usuario"] ?? 0;
$sql_caja_abierta = "SELECT * FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ? AND empresaID = ?";
$rs_caja_abierta = $db->obtenerTodo($sql_caja_abierta, array($usuarioID, $empresaID));
$boton_estado = (count($rs_caja_abierta) > 0) ? "" : "disabled";

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Selección Habitaciones - Mapa Interactivo</title>

    <!-- Bootstrap & Helpers -->
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src='notificaciones.js'></script>

    <!-- Estilos Personalizados (Modular) -->
    <link rel="stylesheet" href="css/habitaciones_interactivo.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-center">
                <?php if ($rs): ?>
                    <?php
                    $tipoActual = "";
                    foreach ($rs as $habitacion):
                        // Lógica de Títulos por Agrupación
                        if (isset($_GET['orden']) && $_GET['orden'] == 'tipo' && $tipoActual != $habitacion['nombre']) {
                            $tipoActual = $habitacion['nombre'];
                            echo '<div class="w-100 mt-4 mb-2"><h4 class="text-primary border-bottom pb-2 fw-bold"><i class="fas fa-tag"></i> ' . mb_strtoupper($tipoActual) . '</h4></div>';
                        }
                        ?>
                        <?php
                        // LÓGICA SMART BIDIRECCIONAL: Sincronización Real de Ocupación
                              // CASO A: Si hay un hospedaje ACTIVO en BD pero la habitación NO está marcada como OCUPADA (ej: está en LIMPIEZA)
                        if (!empty($habitacion['hospedaje_activo_id']) && $habitacion['estado'] !== 'OCUPADA') {
                            $habitacion['estado'] = 'OCUPADA';
                            // Sincronización silenciosa opcional (opcional para no saturar BD, pero asegura coherencia)
                            $db->ejecutar("UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
                        }
                        
                        // CASO B: Si la habitación dice estar ocupada o en deuda, pero NO hay ningún hospedaje ACTIVO en BD
                        else if (in_array($habitacion['estado'], ['OCUPADA', 'DEUDA']) && empty($habitacion['hospedaje_activo_id'])) {
                            $habitacion['estado'] = 'LIMPIEZA';
                            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacion['habitacionID'], $empresaID]);
                        }

                        // CASO C: SINCRONIZACIÓN DE DEUDA (Si la habitación está OCUPADA y el checkout ya pasó)
                        if ($habitacion['estado'] === 'OCUPADA' && !empty($habitacion['checkout_activo'])) {
                            $now_stamp = time();
                            if (strtotime($habitacion['checkout_activo']) < $now_stamp) {
                                $habitacion['estado'] = 'DEUDA';
                                // Persistencia real en la base de datos (SOLO HABITACIÓN)
                                $db->ejecutar("UPDATE habitaciones SET estado = 'DEUDA' WHERE habitacionID = ? AND empresaID = ?", [$habitacion['habitacionID'], $empresaID]);

                                // Regla de Hotelería: calcular deuda (cruzar las 13:00)
                                $checkout_obj = new DateTime($habitacion['checkout_activo']);
                                $ahora_obj = new DateTime();
                                $dias_cobro = 1;
                                $iter_date_limite = clone $checkout_obj;
                                $iter_date_limite->modify('+1 day');
                                $iter_date_limite->setTime(13, 0, 0);
                                while ($iter_date_limite <= $ahora_obj) {
                                    $dias_cobro++;
                                    $iter_date_limite->modify('+1 day');
                                }
                                $precio_diario = !empty($habitacion['precio_pactado']) ? $habitacion['precio_pactado'] : $habitacion['precio'];
                                $habitacion['precio_pactado'] = $dias_cobro * $precio_diario;
                            }
                        }

                        // Asignar la clase de Bootstrap según el estado
                        $btnClass = 'btn-habitacion';
                        switch ($habitacion['estado']) {
                            case 'DISPONIBLE':
                                $btnClass .= ' btn btn-success';
                                break;
                            case 'OCUPADA':
                                $btnClass .= ' btn btn-primary';
                                break;
                            case 'DEUDA':
                                $btnClass .= ' btn btn-danger';
                                break;
                            case 'LIMPIEZA':
                                $btnClass .= ' btn btn-secondary';
                                break;
                            case 'RESERVADA':
                                $btnClass .= ' btn btn-info';
                                break;
                            case 'MOMENTANEO':
                                $btnClass .= ' btn btn-warning';
                                break;
                            default:
                                $btnClass .= ' btn btn-dark';
                        }
                        ?>
                        <button id="habitacion-<?php echo $habitacion['habitacionID']; ?>"
                            class="<?php echo $btnClass; ?> habitacion-card"
                            data-tipo-id="<?php echo $habitacion['tipohabitacionID']; ?>"
                            data-tipo-nombre="<?php echo $habitacion['nombre']; ?>" <?php echo $boton_estado; ?>
                            onclick="handleHabitacionClick('<?php echo $habitacion['estado']; ?>', '<?php echo $habitacion['numero']; ?>', '<?php echo $habitacion['nombre']; ?>', '<?php echo ($habitacion['estado'] === 'DEUDA' ? $habitacion['precio_pactado'] : (!empty($habitacion['precio_pactado']) ? $habitacion['precio_pactado'] : $habitacion['precio'])); ?>', '<?php echo $habitacion['habitacionID']; ?>')">

                            <?php if ($habitacion['estado'] === 'DEUDA'): ?>
                                <span>DEUDA</span>
                                <strong><?php echo $habitacion['numero']; ?></strong>
                            <?php else: ?>
                                <span><?php echo $habitacion['estado']; ?></span>
                                <strong><?php echo $habitacion['numero']; ?></strong>
                            <?php endif; ?>

                            <?php if (($habitacion['estado'] === 'OCUPADA' || $habitacion['estado'] === 'DEUDA') && !empty($habitacion['cliente_activo'])): ?>
                                <!-- Ficha Flotante (Tooltip) para OCUPADAS y DEUDAS -->
                                <?php if ($habitacion['estado'] === 'DEUDA'): ?>
                                    <span class="badge-precio" style="background:#dc3545; color:#fff; border-color:#dc3545;">DEUDA Bs.
                                        <?php echo number_format($habitacion['precio_pactado'], 0); ?></span>
                                <?php else: ?>
                                    <span class="badge-precio">Bs. <?php echo number_format($habitacion['precio_pactado'], 0); ?></span>
                                <?php endif; ?>

                                <div class="habitacion-info-tooltip">
                                    <div class="tooltip-header" <?php echo ($habitacion['estado'] === 'DEUDA') ? 'style="background-color: #dc3545;"' : ''; ?>>
                                        <i
                                            class="fas <?php echo ($habitacion['estado'] === 'DEUDA') ? 'fa-exclamation-triangle' : 'fa-user-circle'; ?>"></i>
                                        <?php echo ($habitacion['estado'] === 'DEUDA') ? 'DEUDA VENCIDA' : 'DETALLE OCUPACIÓN'; ?>
                                    </div>
                                    <div class="tooltip-body">
                                        <p><strong>CLIENTE:</strong><br>
                                            <?php echo mb_strtoupper((string) $habitacion['cliente_activo']); ?></p>
                                        <p><strong>SALIDA:</strong>
                                            <?php echo date('d/m H:i', strtotime($habitacion['checkout_activo'])); ?></p>
                                        <p><strong>TIPO:</strong> <?php echo $habitacion['nombre']; ?></p>
                                    </div>
                                </div>
                            <?php elseif ($habitacion['estado'] === 'DISPONIBLE'): ?>
                                <!-- Precio visible directamente solo en DISPONIBLES -->
                                <span class="badge-precio">Bs. <?php echo number_format($habitacion['precio'], 0); ?></span>

                                <div
                                    style="position: absolute; top: 4px; left: 4px; display: flex; flex-direction: column; gap: 2px; align-items: flex-start;">
                                    <?php if ($habitacion['tv'] == 1): ?>
                                        <span
                                            style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">TV</span>
                                    <?php endif; ?>
                                    <?php if ($habitacion['bano'] == 1): ?>
                                        <span
                                            style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">BAÑO</span>
                                    <?php endif; ?>
                                    <?php if ($habitacion['ventilador'] == 1): ?>
                                        <span
                                            style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">VENT</span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Otros estados: Texto simple -->
                                <span
                                    class="estado-label"><?php echo ($habitacion['estado'] === 'MANTENIMIENTO') ? 'MANT.' : $habitacion['estado']; ?></span>

                                <?php if ($habitacion['estado'] === 'MANTENIMIENTO' && trim((string) $habitacion['descripcion']) !== ''): ?>
                                    <div class="habitacion-info-tooltip">
                                        <div class="tooltip-header"
                                            style="background-color: #343a40; color: white; border-color: #555;">
                                            <i class="fas fa-tools"></i> MANTENIMIENTO
                                        </div>
                                        <div class="tooltip-body">
                                            <p><strong>DESCRIPCIÓN:</strong><br>
                                                <?php echo nl2br(htmlspecialchars($habitacion['descripcion'])); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CARGA DE MODALES (Modular) -->
    <?php include "modales_habitaciones.php"; ?>

    <!-- LÓGICA DE GESTIÓN (Modular) -->
    <script src="js/habitaciones_gestion.js?v=<?php echo time(); ?>"></script>
    <script>
        function filtrarHabitacionesPorTipo(tipoID) {
            const habitaciones = document.querySelectorAll('.habitacion-card');
            habitaciones.forEach(hab => {
                if (tipoID === 'TODOS' || hab.getAttribute('data-tipo-id') === tipoID) {
                    hab.style.display = '';
                    hab.classList.remove('d-none');
                } else {
                    hab.style.display = 'none';
                    hab.classList.add('d-none');
                }
            });
        }
    </script>

</body>

</html>