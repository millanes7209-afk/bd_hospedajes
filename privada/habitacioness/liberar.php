<?php
session_start();
require_once("../../conexion.php");

// Verificar que se recibió el ID de la habitación
if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];

    // Consulta SQL para obtener la información del hospedaje activo
    $sql = $db->Prepare("SELECT * FROM reservas WHERE habitacionID = ? AND estado2 = 'ACTIVO'");
    $reserva = $db->GetRow($sql, array($habitacionID));

    if ($reserva) {
        // Cambiar el estado del hospedaje a INACTIVO
        $updateReservaSQL = $db->Prepare("UPDATE reservas SET estado2 = 'INACTIVO', estado='CANCELADA' WHERE reservaID = ?");
        $db->Execute($updateReservaSQL, array($reserva['reservaID']));

        // Cambiar el estado de la habitación a LIMPIEZA
        $updateHabitacionSQL = $db->Prepare("UPDATE habitaciones SET estado = 'DISPONIBLE' WHERE habitacionID = ?");
        $db->Execute($updateHabitacionSQL, array($habitacionID));

        $_SESSION['message'] = 'La habitación ha sido desocupada y el hospedaje actualizado a INACTIVO.';
    } else {
        $_SESSION['message'] = 'No se encontró un hospedaje activo para esta habitación.';
    }
} else {
    $_SESSION['message'] = 'Faltan datos necesarios.';
}

// Redirigir de vuelta a la página de gestión de habitaciones
header("Location: habitaciones.php"); // Cambia la ruta según sea necesario
exit();
?>
