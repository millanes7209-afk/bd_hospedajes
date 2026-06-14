<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

if (!isset($_SESSION['empresaID'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión expirada']);
    exit;
}

if (isset($_GET['habitacionID']) && isset($_GET['nuevoEstado'])) {
    $habitacionID = $_GET['habitacionID'];
    $nuevoEstado = $_GET['nuevoEstado'];
    $empresaID = $_SESSION['empresaID'];

    // Lógica de limpieza de descripción si pasa a limpieza o disponible
    if ($nuevoEstado === 'LIMPIEZA' || $nuevoEstado === 'DISPONIBLE') {
        $sql = "UPDATE habitaciones SET estado = ?, descripcion = '' WHERE habitacionID = ? AND empresaID = ?";
        $params = array($nuevoEstado, $habitacionID, $empresaID);
    } else {
        $sql = "UPDATE habitaciones SET estado = ? WHERE habitacionID = ? AND empresaID = ?";
        $params = array($nuevoEstado, $habitacionID, $empresaID);
    }

    try {
        $result = $db->ejecutar($sql, $params);
        if ($result) {
            echo json_encode(['success' => true, 'nuevoEstado' => $nuevoEstado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se realizaron cambios']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
}
