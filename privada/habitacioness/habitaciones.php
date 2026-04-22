<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta SQL para obtener habitaciones filtradas por empresa actual
$sql = "SELECT  thab.tipohabitacionID, hab.habitacionID, hab.bano, hab.tv, hab.ventilador, 
                thab.nombre, thab.precio, hab.estado as estado, hab.numero as numero, 
                hab.descripcion as descripcion,
                (SELECT GROUP_CONCAT(CONCAT('- ', c.nombres, ' ', c.apellido1) SEPARATOR '<br>')
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X') AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo,
                (SELECT h.monto 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS precio_pactado
        FROM    habitaciones hab
        JOIN    tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        WHERE   thab._estado <> 'X'
        AND     hab._estado <> 'X'
        AND     hab.empresaID = ?
        ORDER BY hab.numero ASC";



$rs = $db->obtenerTodo($sql, array($empresaID));


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
                    <?php foreach ($rs as $habitacion): ?>
                        <?php
                        // LÓGICA SMART BIDIRECCIONAL: Sincronización Real de Ocupación
                
                        // CASO A: Si hay un cliente activo pero la habitación NO está marcada como OCUPADA (ej: está en LIMPIEZA)
                        if (!empty($habitacion['cliente_activo']) && $habitacion['estado'] !== 'OCUPADA') {
                            $habitacion['estado'] = 'OCUPADA';
                            // Sincronización silenciosa opcional (opcional para no saturar BD, pero asegura coherencia)
                            $db->ejecutar("UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
                        }
                        // CASO B: Si la habitación dice OCUPADA pero en realidad NO hay nadie registrado
                        else if ($habitacion['estado'] === 'OCUPADA' && empty($habitacion['cliente_activo'])) {
                            $habitacion['estado'] = 'LIMPIEZA';
                            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
                        }

                        // CASO C: DEUDA (Tiempo vencido) - La deuda final se calcula cruzando las 13:00
                        $now_stamp = time();
                        if ($habitacion['estado'] === 'OCUPADA' && !empty($habitacion['checkout_activo']) && strtotime($habitacion['checkout_activo']) < $now_stamp) {
                            $habitacion['estado'] = 'DEUDA';
                            
                            // Regla de Hotelería: cobrar 1 día por pasarse de la hora.
                            // Cobrar días adicionales al sobrepasar la barrera de las 13:00 los días siguientes.
                            $checkout_obj = new DateTime($habitacion['checkout_activo']);
                            $ahora_obj = new DateTime();
                            
                            $dias_cobro = 1;

                            $iter_date_limite = clone $checkout_obj;
                            $iter_date_limite->modify('+1 day');
                            $iter_date_limite->setTime(13, 0, 0); // Vencimiento del primer día de castigo

                            while ($iter_date_limite <= $ahora_obj) {
                                $dias_cobro++;
                                $iter_date_limite->modify('+1 day');
                            }
                            
                            $deuda_final = $dias_cobro * $habitacion['precio']; 
                            $habitacion['precio_pactado'] = $deuda_final; // Sobrescribimos visualmente para el botón
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
                        <button id="habitacion-<?php echo $habitacion['habitacionID']; ?>" class="<?php echo $btnClass; ?>"
                            <?php echo $boton_estado; ?>
                            onclick="handleHabitacionClick('<?php echo $habitacion['estado']; ?>', '<?php echo $habitacion['numero']; ?>', '<?php echo $habitacion['nombre']; ?>', '<?php echo $habitacion['precio']; ?>', '<?php echo $habitacion['habitacionID']; ?>')">

                            <strong><?php echo $habitacion['numero']; ?></strong>

                            <?php if (($habitacion['estado'] === 'OCUPADA' || $habitacion['estado'] === 'DEUDA') && !empty($habitacion['cliente_activo'])): ?>
                                <!-- Ficha Flotante (Tooltip) para OCUPADAS y DEUDAS -->
                                <?php if ($habitacion['estado'] === 'DEUDA'): ?>
                                    <span class="badge-precio" style="background:#dc3545; color:#fff; border-color:#dc3545;">DEUDA Bs. <?php echo number_format($habitacion['precio_pactado'], 0); ?></span>
                                <?php else: ?>
                                    <span class="badge-precio">Bs. <?php echo number_format($habitacion['precio_pactado'], 0); ?></span>
                                <?php endif; ?>
                                
                                <div class="habitacion-info-tooltip">
                                    <div class="tooltip-header" <?php echo ($habitacion['estado'] === 'DEUDA') ? 'style="background-color: #dc3545;"' : ''; ?>>
                                        <i class="fas <?php echo ($habitacion['estado'] === 'DEUDA') ? 'fa-exclamation-triangle' : 'fa-user-circle'; ?>"></i> 
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
                            <?php else: ?>
                                <!-- Otros estados: Texto simple -->
                                <span class="estado-label"><?php echo ($habitacion['estado'] === 'MANTENIMIENTO') ? 'MANT.' : $habitacion['estado']; ?></span>
                                
                                <?php if ($habitacion['estado'] === 'MANTENIMIENTO' && trim((string)$habitacion['descripcion']) !== ''): ?>
                                    <div class="habitacion-info-tooltip">
                                        <div class="tooltip-header" style="background-color: #343a40; color: white; border-color: #555;">
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
</body>

</html>