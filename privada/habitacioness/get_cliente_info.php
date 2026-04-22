<?php
session_start();
require_once("../../conexion.php");

// Verificar que se haya recibido el ID de la habitación
if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];

    // Consulta SQL para obtener la información de todos los clientes asociados al hospedaje activo
    $sql = $db->Prepare("SELECT GROUP_CONCAT(CONCAT(c.nombres, ' ', c.apellidos) SEPARATOR ', ') AS cliente, 
                                h.checkout, h.monto_total AS precio, h.descripcion as descripcion
                          FROM hospedajes h 
                          JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
                          JOIN clientes c ON hc.clienteID = c.clienteID
                          WHERE h.habitacionID = ? AND h.estado = 'ACTIVO'");

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
