<?php
// Conectar a la base de datos
require_once 'conexion.php';

// Habilitar el reporte de errores para depurar
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Recibir el dato JSON con el notificacionID
$data = json_decode(file_get_contents("php://input"), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    // Si hay un error en la decodificación JSON
    echo json_encode(['success' => false, 'message' => 'Error al decodificar JSON: ' . json_last_error_msg()]);
    exit;
}
$notificacionID = $data['notificacionID'];

// Verificar si notificacionID no es nulo
if (empty($notificacionID)) {
    echo json_encode(['success' => false, 'message' => 'Notificación ID no proporcionado']);
    exit;
}

// Actualizar el estado de la notificación a 'atendida'
$query = "UPDATE notificaciones SET estado = 'atendida' WHERE notificacionID = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    // Si la consulta no se preparó correctamente
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
    exit;
}
$stmt->bind_param("i", $notificacionID);  // Asegúrate de que el tipo de dato sea el correcto, "i" para entero

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación']);
}
?>
