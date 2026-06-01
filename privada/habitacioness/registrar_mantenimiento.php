<?php
session_start();

require_once("../../conexion.php");

// Obtener los datos del formulario
$descripcion = $_POST['descripcion'];
$habitacionID = $_POST['habitacionID'];
$empresaID = $_SESSION['empresaID'];

// Consulta SQL para actualizar estado y descripción
$sql = "UPDATE habitaciones SET estado = ?, descripcion = ? WHERE habitacionID = ? AND empresaID = ?";

// Ejecutar la consulta - USAR MÉTODO CORRECTO DE LA CLASE MiConexion
try {
    $result = $db->ejecutar($sql, array('MANTENIMIENTO', $descripcion, $habitacionID, $empresaID));
    
    if ($result) {
        $_SESSION['message'] = 'Mantenimiento registrado correctamente';
    } else {
        $_SESSION['message'] = 'Error al registrar mantenimiento';
    }
} catch (Exception $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
}

header("Location: habitaciones.php");
exit();

?>
