<?php
session_start();
require_once '../../conexion.php';

// Recibir el dato JSON con el notificacionID
$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Error al decodificar JSON']);
    exit;
}

$notificacionID = $data['notificacionID'] ?? null;

if (empty($notificacionID)) {
    echo json_encode(['success' => false, 'message' => 'Notificación ID no proporcionado']);
    exit;
}

// Actualizar el estado de la notificación a 'atendida'
$query = "UPDATE notificaciones SET estado = 'atendida' WHERE notificacionID = ?";

if ($db->ejecutar($query, [$notificacionID])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación']);
}
?>
