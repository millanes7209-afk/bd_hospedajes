<?php
session_start();

// Conectar a la base de datos
require_once '../../conexion.php';

// Recibir el dato JSON con el notificacionID
$data = json_decode(file_get_contents("php://input"), true);
$notificacionID = $data['notificacionID'];

// Verificar si notificacionID no es nulo
if (empty($notificacionID)) {
    echo json_encode(['success' => false, 'message' => 'Notificación ID no proporcionado']);
    exit;
}

// Obtener la fecha actual y sumarle 5 minutos
$fecha_programada = date('Y-m-d H:i:s', strtotime('+5 minutes'));

// Actualizar la fecha_programada de la notificación
$query = "UPDATE notificaciones SET fecha_programada = ? WHERE notificacionID = ?";

// Usamos Execute() con parámetros directamente
$result = $db->Execute($query, array($fecha_programada, $notificacionID));

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo posponer la notificación']);
}

?>
