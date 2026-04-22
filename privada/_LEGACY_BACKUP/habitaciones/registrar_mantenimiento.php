<?php
session_start();
require_once("../../conexion.php");

// Obtener datos del formulario
$habitacionID = $_POST['habitacionID'];
$numero = $_POST['numero'];
$descripcion = $_POST['descripcion'];

try {
    // Actualizar el estado de la habitación a MANTENIMIENTO
    $sql = "UPDATE habitaciones SET estado = 'MANTENIMIENTO' WHERE habitacionID = ? AND _estado <> 'X'";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$habitacionID]);
    
    if ($result) {
        // Registrar el mantenimiento en la tabla de mantenimientos (si existe)
        $sql_mantenimiento = "INSERT INTO mantenimientos (habitacionID, numero, descripcion, fecha, _usuario, _fec_insercion, _estado) 
                             VALUES (?, ?, ?, NOW(), ?, NOW(), 'A')";
        $stmt_mantenimiento = $db->prepare($sql_mantenimiento);
        $stmt_mantenimiento->execute([$habitacionID, $numero, $descripcion, $_SESSION['sesion_id_usuario']]);
        
        // Redireccionar de vuelta al listado de habitaciones
        header("Location: habitaciones.php?mensaje=mantenimiento_registrado&habitacion=" . urlencode($numero));
        exit();
    } else {
        header("Location: habitaciones.php?error=error_mantenimiento");
        exit();
    }
} catch (Exception $e) {
    header("Location: habitaciones.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
