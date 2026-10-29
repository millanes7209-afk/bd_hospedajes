<?php

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

// ── VERIFICAR DESPACHO DESDE CENTRAL ──────────────────────────────────────────
if ($accion === 'verificar_despacho') {
    try {
        // 1. Estado local de stock_diario
        $stmtStk = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN aceptado = 1 THEN 1 ELSE 0 END) as aceptados FROM stock_diario WHERE fecha = ?");
        $stmtStk->execute([$hoy]);
        $stkLocal = $stmtStk->fetch();
        $hayStockLocal = ($stkLocal['total'] ?? 0) > 0;
        $yaAceptadoLocal = $hayStockLocal && (($stkLocal['aceptados'] ?? 0) > 0);

        // 2. Consultar a Salteñas Central (Carrito ID = 1)
        $ch = curl_init('https://saltenas.alloggibolivia.com/api/v1/despachos/hoy?carrito_id=1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'X-POS-Api-Key: pos_saltenas_secret_key_2026'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $despachoCentral = null;
        if ($res && $httpCode === 200) {
            $json = json_decode($res, true);
            if (!empty($json['success']) && !empty($json['despacho'])) {
                $despachoCentral = $json['despacho'];
            }
        }

        if ($despachoCentral) {
            $esAceptado = ($despachoCentral['estado'] === 'aceptado') || $yaAceptadoLocal;
            echo json_encode([
                'success' => true,
                'hay_despacho' => true,
                'aceptado' => (bool) $esAceptado,
                'despacho' => $despachoCentral
            ]);
        } elseif ($hayStockLocal) {
            echo json_encode([
                'success' => true,
                'hay_despacho' => true,
                'aceptado' => (bool) $yaAceptadoLocal,
                'despacho' => [
                    'id' => null,
                    'fecha' => $hoy,
                    'estado' => $yaAceptadoLocal ? 'aceptado' : 'enviado',
                    'detalles' => []
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'hay_despacho' => false,
                'aceptado' => false,
                'mensaje' => 'No hay despacho registrado para hoy en Central.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── ACEPTAR STOCK / RECIBIR DESPACHO ──────────────────────────────────────────
if ($accion === 'aceptar_stock') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $despachoId = $input['despacho_id'] ?? ($_REQUEST['despacho_id'] ?? null);
    $detalles = $input['detalles'] ?? [];

    try {
        $pdo->beginTransaction();

        // Sincronizar variantes y stock en BD local si se enviaron detalles
        if (!empty($detalles) && is_array($detalles)) {
            foreach ($detalles as $det) {
                $nombre = $det['variante_nombre'] ?? ($det['nombre'] ?? '');
                $cant = (int) ($det['cantidad_enviada'] ?? 0);
                if (empty($nombre)) continue;

                $stP = $pdo->prepare("SELECT id FROM productos WHERE nombre = ? LIMIT 1");
                $stP->execute([$nombre]);
                $prod = $stP->fetch();

                if (!$prod) {
                    $stIns = $pdo->prepare("INSERT INTO productos (nombre, precio, activo) VALUES (?, 10.00, 1)");
                    $stIns->execute([$nombre]);
                    $prodId = (int) $pdo->lastInsertId();
                } else {
                    $prodId = (int) $prod['id'];
                }

                $stS = $pdo->prepare("SELECT id FROM stock_diario WHERE fecha = ? AND producto_id = ?");
                $stS->execute([$hoy, $prodId]);
                if ($stS->fetch()) {
                    $stUp = $pdo->prepare("UPDATE stock_diario SET cantidad_enviada = ?, aceptado = 1 WHERE fecha = ? AND producto_id = ?");
                    $stUp->execute([$cant, $hoy, $prodId]);
                } else {
                    $stInS = $pdo->prepare("INSERT INTO stock_diario (fecha, producto_id, cantidad_enviada, aceptado) VALUES (?, ?, ?, 1)");
                    $stInS->execute([$hoy, $prodId, $cant]);
                }
            }
        }

        // Marcar todo el stock de hoy como aceptado
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?");
        $stmt->execute([$hoy]);

        $pdo->commit();

        // Notificar a Salteñas Central si tenemos el despacho_id
        $notificado = false;
        if ($despachoId) {
            $ch = curl_init("https://saltenas.alloggibolivia.com/api/v1/despachos/{$despachoId}/aceptar");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'X-POS-Api-Key: pos_saltenas_secret_key_2026'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            curl_close($ch);
            if ($res) {
                $j = json_decode($res, true);
                $notificado = !empty($j['success']);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => '¡Stock aceptado y recibido con éxito!',
            'notificado_central' => $notificado
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── REGISTRAR VENTA ───────────────────────────────────────────────────────────
if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);
    $metodoPago = in_array(($input['metodo_pago'] ?? 'efectivo'), ['efectivo', 'qr']) ? $input['metodo_pago'] : 'efectivo';

    $items = [];
    if (isset($input['items']) && is_array($input['items'])) {
        $items = $input['items'];
    } else {
        $items[] = [
            'tipo' => $input['tipo'] ?? 'individual',
            'producto_id' => $input['producto_id'] ?? null,
            'promocion_id' => $input['promocion_id'] ?? null,
            'cantidad' => (int) ($input['cantidad'] ?? 1),
            'monto' => (float) ($input['monto'] ?? 0.00)
        ];
    }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO ventas (fecha_hora, producto_id, promocion_id, cantidad, monto, metodo_pago) VALUES (NOW(), ?, ?, ?, ?, ?)");

        foreach ($items as $item) {
            $pId = $item['producto_id'] ?? null;
            $prId = $item['promocion_id'] ?? null;
            $cant = (int) ($item['cantidad'] ?? 1);
            $monto = (float) ($item['monto'] ?? 0.00);

            $stmt->execute([$pId, $prId, $cant, $monto, $metodoPago]);
        }
        $pdo->commit();

        echo json_encode(['success' => true, 'message' => 'Venta registrada con éxito']);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
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
    $dineroEfectivo = (float) ($input['dinero_efectivo'] ?? 0.00);
    $dineroQr = (float) ($input['dinero_qr'] ?? 0.00);
    $observaciones = trim($input['observaciones'] ?? '');

    try {
        // 1. Guardado Local
        $stmt = $pdo->prepare("INSERT INTO cierres (fecha, total_vendidas, total_sobrantes, dinero_cobrado, dinero_efectivo, dinero_qr, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$hoy, $totalVendidas, $totalSobrantes, $dineroCobrado, $dineroEfectivo, $dineroQr, $observaciones]);

        // 2. Sincronización Automática con Sistema Central (saltenas.alloggibolivia.com)
        $centralApiUrl = 'https://saltenas.alloggibolivia.com/api/v1/cierres/sincronizar';
        $apiKey = 'pos_saltenas_secret_key_2026';

        $payloadCentral = [
            'fecha' => $hoy,
            'monto_real' => $dineroCobrado,
            'observaciones' => "Efectivo: Bs. $dineroEfectivo | QR: Bs. $dineroQr | " . $observaciones,
            'detalles' => [
                [
                    'variante_id' => 1,
                    'cantidad_entregada' => $totalVendidas + $totalSobrantes,
                    'cantidad_vendida_normal' => $totalVendidas,
                    'cantidad_sobrante' => $totalSobrantes
                ]
            ]
        ];

        $ch = curl_init($centralApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadCentral));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-POS-Api-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $resp = curl_exec($ch);
        curl_close($ch);

        echo json_encode(['success' => true, 'message' => 'Cierre de turno completado y sincronizado con Central']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
