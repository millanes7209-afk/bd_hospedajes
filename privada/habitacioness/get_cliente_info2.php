<?php
session_start();
require_once("../../conexion.php");

// Verificar que se haya recibido el ID de la habitación
if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];

    // Consulta SQL para obtener la información del cliente asociado al hospedaje activo
    $sql = $db->Prepare("SELECT CONCAT(c.nombres, ' ', c.apellidos) AS cliente, h.checkin, h.monto_reserva AS precio 
                          FROM reservas h 
                          JOIN clientes c ON h.clienteID = c.clienteID 
                          WHERE h.habitacionID = ? AND h.estado2 = 'ACTIVO'");
    
    $hospedajeInfo = $db->GetRow($sql, array($habitacionID));
    
    if ($hospedajeInfo) {
        // Devolver la información como JSON
        echo json_encode($hospedajeInfo);
    } else {
        // Si no hay información, devolver un mensaje vacío
        echo json_encode([]);
    }
} else {
    // Si no se proporciona un ID de habitación, devolver un error
    echo json_encode(['error' => 'Faltan datos necesarios.']);
}
?>
