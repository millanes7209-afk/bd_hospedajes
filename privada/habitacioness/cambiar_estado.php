<?php
session_start();
require_once("../../conexion.php");

// Verificar que se recibió el ID de la habitación y el nuevo estado
if (isset($_GET['habitacionID']) && isset($_GET['nuevoEstado'])) {
    $habitacionID = $_GET['habitacionID'];
    $nuevoEstado = $_GET['nuevoEstado'];

    $empresaID = $_SESSION['empresaID'];
    
    // Consulta SQL simple - solo cambiar estado
    if ($nuevoEstado === 'LIMPIEZA') {
        $sql = "UPDATE habitaciones SET estado = ?, descripcion = '' WHERE habitacionID = ? AND empresaID = ?";
        $params = array($nuevoEstado, $habitacionID, $empresaID);
    } else {
        $sql = "UPDATE habitaciones SET estado = ? WHERE habitacionID = ? AND empresaID = ?";
        $params = array($nuevoEstado, $habitacionID, $empresaID);
    }
    
    // Ejecutar la consulta - USAR MÉTODO CORRECTO DE LA CLASE MiConexion
    try {
        $result = $db->ejecutar($sql, $params);
        
        if ($result) {
            $_SESSION['message'] = 'Estado actualizado a ' . $nuevoEstado; // Mensaje de éxito
        } else {
            $_SESSION['message'] = 'Error al actualizar estado'; // Mensaje de error
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage(); // Mensaje de error
    }
} else {
    $_SESSION['message'] = 'Datos inválidos'; // Mensaje de error
}

// Redirigir de vuelta a la página principal de gestión de habitaciones
header("Location: habitaciones.php");
exit();
?>
