<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$empresaID = $_SESSION['empresaID'] ?? null;
$usuarioID = $_SESSION['sesion_id_usuario'] ?? null;

if (!$empresaID || !$usuarioID) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit;
}

$accion = $_GET['accion'] ?? '';

if ($accion === 'obtener') {
    try {
        $sql = "SELECT saldo_efectivo, saldo_qr, saldo_total
                FROM caja_tienda
                WHERE empresaID = ? AND _estado = 'A'
                ORDER BY caja_tiendaID DESC
                LIMIT 1";
        $caja = $db->obtenerFila($sql, [$empresaID]);

        if ($caja) {
            echo json_encode(['status' => 'ok', 'data' => $caja]);
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'No se encontró caja para esta empresa']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'mensaje' => 'Error al obtener caja: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida']);
}
