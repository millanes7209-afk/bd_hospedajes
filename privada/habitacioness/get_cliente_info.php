<?php
session_start();
require_once("../../conexion.php");

// Verificar que se haya recibido el ID de la habitación
if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];

    // Consulta SQL para obtener la información de todos los clientes asociados al hospedaje activo
    $sql = "SELECT GROUP_CONCAT(CONCAT(c.nombres, ' ', c.apellidos) SEPARATOR ', ') AS cliente, 
                   h.checkout, h.monto_total AS precio, h.descripcion as descripcion
            FROM hospedajes h 
            JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
            JOIN clientes c ON hc.clienteID = c.clienteID
            WHERE h.habitacionID = ? AND h.estado IN ('ACTIVO', 'DEUDA') AND h._estado <> 'X'";

    $hospedajeInfo = $db->obtenerFila($sql, [$habitacionID]);

    if ($hospedajeInfo) {
        // Devolver la información como JSON
        echo json_encode($hospedajeInfo);
    } else {
        // Si no hay información, devolver un mensaje vacío
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'Faltan datos necesarios.']);
}
?>
