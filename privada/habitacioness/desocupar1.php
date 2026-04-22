<?php
session_start();
require_once("../../conexion.php");

// Verificar que se recibió el ID de la habitación
if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];



        // Cambiar el estado de la habitación a LIMPIEZA
        $updateHabitacionSQL = $db->Prepare("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?");
        $db->Execute($updateHabitacionSQL, array($habitacionID));

}
// Redirigir de vuelta a la página de gestión de habitaciones
header("Location: habitaciones.php"); // Cambia la ruta según sea necesario
exit();
?>
