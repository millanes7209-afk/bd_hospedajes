<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta SQL para obtener habitaciones filtradas por empresa actual
$sql = "SELECT  thab.tipohabitacionID, hab.habitacionID, hab.bano, hab.tv, hab.ventilador, 
                thab.nombre, thab.precio, hab.estado as estado, hab.numero as numero, 
                hab.descripcion as descripcion,
                (SELECT CONCAT(c.nombres, ' ', c.apellido1) 
                 FROM hospedajes h 
                 JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X' AND hc._estado <> 'X' AND c._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS cliente_activo,
                (SELECT h.checkout 
                 FROM hospedajes h 
                 WHERE h.habitacionID = hab.habitacionID 
                 AND h.estado = 'ACTIVO' AND h._estado <> 'X'
                 ORDER BY h.hospedajeID DESC LIMIT 1) AS checkout_activo
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
    <link rel="stylesheet" href="css/habitaciones_backup.css?v=<?php echo time(); ?>">
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

                            <?php if ($habitacion['estado'] === 'OCUPADA' && !empty($habitacion['cliente_activo'])): ?>
                                <!-- Renderizado Smart OCUPADA -->
                                <div style="font-size: 11px; font-weight: bold; line-height: 1.1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 100%; margin-bottom: 2px;"
                                    title="<?php echo $habitacion['cliente_activo']; ?>">
                                    <i class="fas fa-user mr-1"></i>
                                    <?php echo mb_strtoupper((string) $habitacion['cliente_activo']); ?>
                                </div>
                                <strong><?php echo $habitacion['numero']; ?></strong>
                                <div style="font-size: 10px; margin-top: 2px; opacity: 0.9;">
                                    <i class="fas fa-sign-out-alt mr-1"></i>
                                    <?php echo date('d/m H:i', strtotime($habitacion['checkout_activo'])); ?>
                                </div>
                            <?php else: ?>
                                <!-- Renderizado Estándar -->
                                <?php if ($habitacion['estado'] === 'MANTENIMIENTO'): ?>
                                    <!-- Mostrar descripción cuando está en mantenimiento -->
                                    <span
                                        style="font-size: 10px; line-height: 1.2;"><?php echo $habitacion['descripcion'] ?? 'S/M DESCRIPCIÓN'; ?></span>
                                <?php else: ?>
                                    <!-- Mostrar estado normal -->
                                    <span><?php echo $habitacion['estado']; ?></span>
                                    <?php if ($habitacion['estado'] === 'DISPONIBLE'): ?>
                                        <div style="font-size: 14px; font-weight: bold; margin-top: 2px;">Bs. <?php echo number_format($habitacion['precio'], 0); ?></div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <strong><?php echo $habitacion['numero']; ?></strong>
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