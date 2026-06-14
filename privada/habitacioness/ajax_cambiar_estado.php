<?php
session_start();
require_once("../../conexion.php");

// Verificación básica de sesión
if (!isset($_SESSION['sesion_id_usuario']) || !isset($_GET['habitacionID']) || !isset($_GET['nuevoEstado'])) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos o sesión expirada']);
    exit;
}

$habitacionID = $_GET['habitacionID'];
$nuevoEstado = $_GET['nuevoEstado'];
$empresaID = $_SESSION['empresaID'];
$ahora = date("Y-m-d H:i:s");
$usuarioID = $_SESSION['sesion_id_usuario'];

try {
    $sql = "UPDATE habitaciones SET 
            estado = ?, 
            _fec_modificacion = ?, 
            _usuario = ? 
            WHERE habitacionID = ? AND empresaID = ?";

    $params = [$nuevoEstado, $ahora, $usuarioID, $habitacionID, $empresaID];
    $db->ejecutar($sql, $params);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>