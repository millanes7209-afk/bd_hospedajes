<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

if ($accion === 'aceptar_stock') {
    try {
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?");
        $stmt->execute([$hoy]);
        echo json_encode(['success' => true, 'message' => '¡Stock aceptado con éxito!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);
    $tipo = $input['tipo'] ?? 'individual';
    $productoId = $input['producto_id'] ?? null;
    $promocionId = $input['promocion_id'] ?? null;
    $cantidad = (int) ($input['cantidad'] ?? 1);
    $monto = (float) ($input['monto'] ?? 0.00);
    $metodoPago = in_array(($input['metodo_pago'] ?? 'efectivo'), ['efectivo', 'qr']) ? $input['metodo_pago'] : 'efectivo';

    try {
        $stmt = $pdo->prepare("INSERT INTO ventas (fecha_hora, producto_id, promocion_id, cantidad, monto, metodo_pago) VALUES (NOW(), ?, ?, ?, ?, ?)");
        $stmt->execute([$productoId, $promocionId, $cantidad, $monto, $metodoPago]);

        echo json_encode(['success' => true, 'message' => 'Venta registrada']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

if ($accion === 'cerrar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    $totalVendidas = (int) ($input['total_vendidas'] ?? 0);
    $totalSobrantes = (int) ($input['total_sobrantes'] ?? 0);
    $dineroCobrado = (float) ($input['dinero_cobrado'] ?? 0.00);
    $dineroEfectivo = (float) ($input['dinero_efectivo'] ?? 0.00);
    $dineroQr = (float) ($input['dinero_qr'] ?? 0.00);
    $observaciones = trim($input['observaciones'] ?? '');

    try {
        $stmt = $pdo->prepare("INSERT INTO cierres (fecha, total_vendidas, total_sobrantes, dinero_cobrado, dinero_efectivo, dinero_qr, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hoy, $totalVendidas, $totalSobrantes, $dineroCobrado, $dineroEfectivo, $dineroQr, $observaciones]);

        echo json_encode(['success' => true, 'message' => 'Cierre de turno completado']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
