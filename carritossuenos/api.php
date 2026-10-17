<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/saltenas_api.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

// ── ACEPTAR STOCK ──────────────────────────────────────────────────────────────
if ($accion === 'aceptar_stock') {
    try {
        // 1. Marcar como aceptado en la BD local
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?");
        $stmt->execute([$hoy]);

        // 2. Obtener el despacho_central_id para notificar a saltenas
        $stmtId = $pdo->prepare("SELECT despacho_central_id FROM stock_diario WHERE fecha = ? AND despacho_central_id IS NOT NULL LIMIT 1");
        $stmtId->execute([$hoy]);
        $row = $stmtId->fetch();

        $despachoCentralId = $row['despacho_central_id'] ?? ($_SESSION['despacho_central_id'] ?? null);

        $notificado = false;
        if ($despachoCentralId) {
            $resp = saltenas_api('PATCH', "/despachos/{$despachoCentralId}/aceptar");
            $notificado = !empty($resp['success']);
        }

        echo json_encode([
            'success' => true,
            'message' => '¡Stock aceptado con éxito!',
            'notificado' => $notificado,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── REGISTRAR VENTA ────────────────────────────────────────────────────────────
if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);
    $tipo = $input['tipo'] ?? 'individual';
    $productoId = $input['producto_id'] ?? null;
    $promocionId = $input['promocion_id'] ?? null;
    $cantidad = (int) ($input['cantidad'] ?? 1);
    $monto = (float) ($input['monto'] ?? 0.00);

    try {
        $stmt = $pdo->prepare("INSERT INTO ventas (fecha_hora, producto_id, promocion_id, cantidad, monto) VALUES (NOW(), ?, ?, ?, ?)");
        $stmt->execute([$productoId, $promocionId, $cantidad, $monto]);
        echo json_encode(['success' => true, 'message' => 'Venta registrada']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── CERRAR TURNO ───────────────────────────────────────────────────────────────
if ($accion === 'cerrar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    $totalVendidas = (int) ($input['total_vendidas'] ?? 0);
    $totalSobrantes = (int) ($input['total_sobrantes'] ?? 0);
    $dineroCobrado = (float) ($input['dinero_cobrado'] ?? 0.00);
    $observaciones = trim($input['observaciones'] ?? '');

    try {
        // Verificar si ya hay un cierre hoy
        $existe = $pdo->prepare("SELECT COUNT(*) FROM cierres WHERE fecha = ?");
        $existe->execute([$hoy]);
        if ($existe->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'El turno de hoy ya fue cerrado.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO cierres (fecha, total_vendidas, total_sobrantes, dinero_cobrado, observaciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$hoy, $totalVendidas, $totalSobrantes, $dineroCobrado, $observaciones]);

        echo json_encode(['success' => true, 'message' => 'Cierre de turno completado']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
