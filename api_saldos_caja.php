<?php
session_start();
require_once("conexion.php");

header('Content-Type: application/json');

// Verificar si hay caja abierta
if (!isset($_SESSION["sesion_id_usuario"]) || !isset($_SESSION['empresaID']) || !isset($_SESSION['caja_abierta_id'])) {
    echo json_encode(['error' => 'No hay caja abierta']);
    exit();
}

$caja_id = $_SESSION['caja_abierta_id'];
$empresaID = $_SESSION['empresaID'];

try {
    // Obtener información de la caja incluyendo fecha de apertura
    $sql_caja = "SELECT cajaID, fecha_apertura FROM cajas WHERE cajaID = ? AND empresaID = ?";
    $info_caja = $db->obtenerFila($sql_caja, array($caja_id, $empresaID));
    
    // Consulta SQL para obtener saldos SOLO de formas de pago usadas en esta caja
    $sql = "SELECT fp.formapagoID, fp.tipo, fp.descripcion, SUM(m.monto) as total_monto
            FROM movimientos m
            INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
            WHERE m.cajaID = ? AND m.empresaID = ?
            GROUP BY fp.formapagoID
            HAVING SUM(m.monto) > 0
            ORDER BY fp.tipo";
    
    $saldos = $db->obtenerTodo($sql, array($caja_id, $empresaID));
    
    // Calcular total general
    $total_general = 0;
    foreach ($saldos as $saldo) {
        $total_general += $saldo['total_monto'];
    }
    
    // Respuesta JSON
    echo json_encode([
        'success' => true,
        'saldos' => $saldos,
        'total_general' => number_format($total_general, 2, '.', ','),
        'caja_id' => $caja_id,
        'fecha_apertura' => $info_caja ? date('d/m/Y H:i:s', strtotime($info_caja['fecha_apertura'])) : 'N/A',
        'debug' => [
            'caja_id' => $caja_id,
            'empresaID' => $empresaID,
            'info_caja' => $info_caja,
            'saldos_count' => count($saldos),
            'sql_debug' => $sql
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al consultar saldos: ' . $e->getMessage()]);
}
?>
