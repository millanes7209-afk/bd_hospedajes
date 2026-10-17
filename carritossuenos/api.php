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

// ── VERIFICAR DESPACHO DESDE CENTRAL (polling) ────────────────────────────────
if ($accion === 'verificar_despacho') {
    try {
        // ¿Ya hay stock local hoy?
        $stmtEx = $pdo->prepare("SELECT COUNT(*) FROM stock_diario WHERE fecha = ?");
        $stmtEx->execute([$hoy]);
        if ($stmtEx->fetchColumn() > 0) {
            // Ya hay stock, no hace falta consultar central
            echo json_encode(['success' => true, 'hay_despacho' => true, 'ya_existia' => true]);
            exit;
        }

        $resp = saltenas_api('GET', '/despachos/hoy?carrito_id=' . SALTENAS_CARRITO_ID);

        if ($resp === null) {
            echo json_encode(['success' => false, 'error' => 'Sin conexión con Central']);
            exit;
        }

        if (!empty($resp['sin_despacho']) || empty($resp['success']) || empty($resp['despacho'])) {
            echo json_encode(['success' => true, 'hay_despacho' => false]);
            exit;
        }

        // Llegó un despacho nuevo → insertarlo en local
        $despachoRemoto = $resp['despacho'];
        $_SESSION['despacho_central_id'] = $despachoRemoto['id'];

        // Asegurar columna
        try {
            $pdo->exec("ALTER TABLE stock_diario ADD COLUMN IF NOT EXISTS despacho_central_id INT NULL DEFAULT NULL");
        } catch (Exception $e) {
        }

        foreach ($despachoRemoto['detalles'] as $det) {
            $stmtProd = $pdo->prepare("SELECT id FROM productos WHERE nombre = ? AND activo = 1 LIMIT 1");
            $stmtProd->execute([$det['variante_nombre']]);
            $prod = $stmtProd->fetch();

            if (!$prod) {
                $stmtIns = $pdo->prepare("INSERT INTO productos (nombre, precio, activo) VALUES (?, 10.00, 1)");
                $stmtIns->execute([$det['variante_nombre']]);
                $productoId = (int) $pdo->lastInsertId();
            } else {
                $productoId = (int) $prod['id'];
            }

            $stmtStock = $pdo->prepare("INSERT IGNORE INTO stock_diario (fecha, producto_id, cantidad_enviada, aceptado, despacho_central_id) VALUES (?, ?, ?, 0, ?)");
            $stmtStock->execute([$hoy, $productoId, (int) $det['cantidad_enviada'], $despachoRemoto['id']]);
        }

        if (($despachoRemoto['estado'] ?? '') === 'aceptado') {
            $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?")->execute([$hoy]);
        }

        echo json_encode(['success' => true, 'hay_despacho' => true, 'nuevo' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
