<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Obtener empresaID desde la sesión
$empresaID = $_SESSION['empresaID'] ?? 0;

// Consulta SQL para obtener habitaciones filtradas por empresa actual
$sql = "SELECT  thab.tipohabitacionID, hab.habitacionID, hab.bano, hab.tv, hab.ventilador, 
                thab.nombre, thab.precio, hab.estado as estado, hab.numero as numero, 
                hab.descripcion as descripcion
        FROM    habitaciones hab, tipo_habitaciones thab
        WHERE   hab.tipohabitacionID = thab.tipohabitacionID
        AND     thab._estado <> 'X'
        AND     hab._estado <> 'X'
        AND     hab.empresaID = ?
        ORDER BY hab.numero ASC";

$rs = $db->obtenerTodo($sql, array($empresaID));

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Interactivo de Habitaciones</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Helpers -->
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src='js/notificaciones.js'></script>
    
    <!-- Estilos (Ruta Local) -->
    <link rel="stylesheet" href="css/habitaciones_interactivo.css">
</head>
<body>
    <div class="card">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">MAPA INTERACTIVO</h5>
            
            <!-- Referencia de colores -->
            <div class="d-none d-md-flex gap-2">
                <span class="badge bg-success">DISPONIBLE</span>
                <span class="badge bg-primary">OCUPADA</span>
                <span class="badge bg-info">RESERVADA</span>
                <span class="badge bg-danger">DEUDA</span>
                <span class="badge bg-secondary">LIMPIEZA</span>
                <span class="badge bg-dark">MANTENIMIENTO</span>
            </div>
 
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-light" onclick="mostrarModalIngreso()" <?php echo $boton_estado; ?>>Ingresos</button>
                <button type="button" class="btn btn-sm btn-outline-light" onclick="mostrarModalEgreso()" <?php echo $boton_estado; ?>>Egresos</button>
            </div>
        </div>

        <div class="card-body">
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 justify-content-center">
                <?php if ($rs) : ?>
                    <?php foreach ($rs as $habitacion) : ?>
                        <?php
                        // Asignar la clase de Bootstrap según el estado
                        $btnClass = 'btn-habitacion w-100 p-3 shadow-sm d-flex flex-column align-items-center justify-content-center';
                        switch ($habitacion['estado']) {
                            case 'DISPONIBLE': $btnClass .= ' btn btn-success'; break;
                            case 'OCUPADA':    $btnClass .= ' btn btn-primary'; break;
                            case 'DEUDA':      $btnClass .= ' btn btn-danger'; break;
                            case 'LIMPIEZA':   $btnClass .= ' btn btn-secondary'; break;
                            case 'RESERVADA':  $btnClass .= ' btn btn-info'; break;
                            case 'MOMENTANEO': $btnClass .= ' btn btn-warning'; break;    
                            default:           $btnClass .= ' btn btn-dark';
                        }
                        ?>
                        <div class="col">
                            <button id="habitacion-<?php echo $habitacion['habitacionID']; ?>" 
                                class="<?php echo $btnClass; ?>"
                                <?php echo $boton_estado; ?> 
                                onclick="handleHabitacionClick('<?php echo $habitacion['estado']; ?>', '<?php echo $habitacion['numero']; ?>', '<?php echo $habitacion['nombre']; ?>', '<?php echo $habitacion['precio']; ?>', '<?php echo $habitacion['habitacionID']; ?>')">
                                
                                <span class="small fw-normal"><?php echo $habitacion['estado'] ?></span>
                                <strong class="fs-4"><?php echo $habitacion['numero']; ?></strong>
                                                        
                                <div class="info mt-1 small" id="info-<?php echo $habitacion['habitacionID']; ?>"></div>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CARGA DE MODALES (Misma carpeta) -->
    <?php include "modales_habitaciones.php"; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- LÓGICA DE GESTIÓN (Carpeta local js/) -->
    <script src="js/habitaciones_gestion.js"></script>
</body>
</html>
