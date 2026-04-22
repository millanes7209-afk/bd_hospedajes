<?php
session_start();
require_once("../../conexion.php");

// Obtener parámetros
$habitacionID = $_GET['habitacionID'];
$nuevoEstado = $_GET['nuevoEstado'];

try {
    // Actualizar el estado de la habitación
    $sql = "UPDATE habitaciones SET estado = ? WHERE habitacionID = ? AND _estado <> 'X'";
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([$nuevoEstado, $habitacionID]);
    
    if ($result) {
        // Redireccionar de vuelta al listado de habitaciones
        header("Location: habitaciones.php?mensaje=estado_actualizado&estado=" . urlencode($nuevoEstado));
        exit();
    } else {
        header("Location: habitaciones.php?error=error_actualizar");
        exit();
    }
} catch (Exception $e) {
    header("Location: habitaciones.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
