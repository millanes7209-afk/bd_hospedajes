<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'] ?? 0;
$cajaID = $_SESSION['caja_abierta_id'] ?? 0;

if (!$cajaID) {
    echo json_encode(['status' => 'error', 'message' => 'Sin caja abierta']);
    exit;
}

// Obtener sumatoria de ingresos y egresos de baños para esta caja
$sql = "SELECT 
            SUM(CASE WHEN tipo = 'INGRESO' THEN monto ELSE 0 END) as ingresos,
            SUM(CASE WHEN tipo = 'EGRESO' THEN monto ELSE 0 END) as egresos
        FROM banos 
        WHERE cajaID = ? AND empresaID = ?";

$res = $db->obtenerFila($sql, [$cajaID, $empresaID]);

$total = ($res['ingresos'] ?? 0) - ($res['egresos'] ?? 0);

echo json_encode([
    'status' => 'ok',
    'ingresos' => (float)$res['ingresos'],
    'egresos' => (float)$res['egresos'],
    'total' => (float)$total
]);
?>
