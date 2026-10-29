<?php

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

// Asegurar soporte de tabla despachos_recibidos y pago híbrido
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `despachos_recibidos` (
        `despacho_id` INT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `recibido_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("ALTER TABLE ventas MODIFY COLUMN metodo_pago VARCHAR(20) NOT NULL DEFAULT 'efectivo'");
    $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS monto_efectivo DECIMAL(10,2) NOT NULL DEFAULT 0.00");
    $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS monto_qr DECIMAL(10,2) NOT NULL DEFAULT 0.00");
} catch (Exception $e) {}

// ── VERIFICAR DESPACHO DESDE CENTRAL (Detección dinámica) ─────────────────────
if ($accion === 'verificar_despacho') {
    try {
        // 1. Estado local de stock_diario
        $stmtStk = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN aceptado = 1 THEN 1 ELSE 0 END) as aceptados FROM stock_diario WHERE fecha = ?");
        $stmtStk->execute([$hoy]);
        $stkLocal = $stmtStk->fetch();
        $hayStockLocal = ($stkLocal['total'] ?? 0) > 0;
        $yaAceptadoLocal = $hayStockLocal && (($stkLocal['aceptados'] ?? 0) > 0);

        // 2. Consultar a Salteñas Central (sin ID estático quemado)
        $ch = curl_init('https://saltenas.alloggibolivia.com/api/v1/despachos/hoy');
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
            $despachoId = (int) ($despachoCentral['id'] ?? 0);
            $yaAceptadoLocalmente = false;
            if ($despachoId > 0) {
                $stCheck = $pdo->prepare("SELECT despacho_id FROM despachos_recibidos WHERE despacho_id = ?");
                $stCheck->execute([$despachoId]);
                $yaAceptadoLocalmente = (bool) $stCheck->fetch();
            }
            $esAceptado = ($despachoCentral['estado'] === 'aceptado') || $yaAceptadoLocalmente;
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

// ── ACEPTAR STOCK / RECIBIR DESPACHO (Con precios reales de Central y acumulación) ───
if ($accion === 'aceptar_stock') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $despachoId = $input['despacho_id'] ?? ($_REQUEST['despacho_id'] ?? null);
    $detalles = $input['detalles'] ?? [];

    try {
        $pdo->beginTransaction();

        // Si no vienen detalles por POST, obtenerlos de Central
        if (empty($detalles)) {
            $ch = curl_init('https://saltenas.alloggibolivia.com/api/v1/despachos/hoy');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'X-POS-Api-Key: pos_saltenas_secret_key_2026'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $res = curl_exec($ch);
            curl_close($ch);
            if ($res) {
                $j = json_decode($res, true);
                if (!empty($j['despacho'])) {
                    $despachoId = $despachoId ?: ($j['despacho']['id'] ?? null);
                    $detalles = $j['despacho']['detalles'] ?? [];
                }
            }
        }

        // 1. Verificar si este despacho ya fue procesado localmente
        $yaProcesado = false;
        if ($despachoId) {
            $stCheck = $pdo->prepare("SELECT despacho_id FROM despachos_recibidos WHERE despacho_id = ?");
            $stCheck->execute([$despachoId]);
            if ($stCheck->fetch()) {
                $yaProcesado = true;
            }
        }

        // 2. Si no ha sido procesado, procesar detalles y sumar al stock acumulado del día
        if (!$yaProcesado && !empty($detalles) && is_array($detalles)) {
            foreach ($detalles as $det) {
                $nombre = $det['variante_nombre'] ?? ($det['nombre'] ?? '');
                $cant = (int) ($det['cantidad_enviada'] ?? 0);
                $precio = (isset($det['precio']) && $det['precio'] !== null && $det['precio'] !== '') ? (float) $det['precio'] : null;
                if (empty($nombre)) continue;

                $stP = $pdo->prepare("SELECT id FROM productos WHERE nombre = ? LIMIT 1");
                $stP->execute([$nombre]);
                $prod = $stP->fetch();

                if (!$prod) {
                    $stIns = $pdo->prepare("INSERT INTO productos (nombre, precio, activo) VALUES (?, ?, 1)");
                    $stIns->execute([$nombre, $precio]);
                    $prodId = (int) $pdo->lastInsertId();
                } else {
                    $prodId = (int) $prod['id'];
                    // Actualizar precio real solo si viene un precio válido desde Central (sin valor por defecto)
                    if ($precio !== null) {
                        $stUpP = $pdo->prepare("UPDATE productos SET precio = ?, activo = 1 WHERE id = ?");
                        $stUpP->execute([$precio, $prodId]);
                    }
                }

                // Sumar al stock diario de hoy (permite múltiples despachos en el día)
                $stS = $pdo->prepare("SELECT id, cantidad_enviada FROM stock_diario WHERE fecha = ? AND producto_id = ?");
                $stS->execute([$hoy, $prodId]);
                $rowStock = $stS->fetch();

                if ($rowStock) {
                    $stUp = $pdo->prepare("UPDATE stock_diario SET cantidad_enviada = cantidad_enviada + ?, aceptado = 1 WHERE id = ?");
                    $stUp->execute([$cant, $rowStock['id']]);
                } else {
                    $stInS = $pdo->prepare("INSERT INTO stock_diario (fecha, producto_id, cantidad_enviada, aceptado) VALUES (?, ?, ?, 1)");
                    $stInS->execute([$hoy, $prodId, $cant]);
                }
            }

            if ($despachoId) {
                $stRec = $pdo->prepare("INSERT IGNORE INTO despachos_recibidos (despacho_id, fecha) VALUES (?, ?)");
                $stRec->execute([$despachoId, $hoy]);
            }
        }

        // Marcar todo el stock de hoy como aceptado
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE fecha = ?");
        $stmt->execute([$hoy]);

        $pdo->commit();

        // Notificar a Salteñas Central el despacho aceptado
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

// ── REGISTRAR VENTA (Con soporte para Pago Híbrido: Efectivo + QR) ─────────────
if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);
    $metodoPago = in_array(($input['metodo_pago'] ?? 'efectivo'), ['efectivo', 'qr', 'hibrido']) ? $input['metodo_pago'] : 'efectivo';
    $montoEfectivo = (float) ($input['monto_efectivo'] ?? 0.00);
    $montoQr = (float) ($input['monto_qr'] ?? 0.00);

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
        $stmt = $pdo->prepare("INSERT INTO ventas (fecha_hora, producto_id, promocion_id, cantidad, monto, metodo_pago, monto_efectivo, monto_qr) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?)");

        $totalItems = count($items);
        foreach ($items as $idx => $item) {
            $pId = $item['producto_id'] ?? null;
            $prId = $item['promocion_id'] ?? null;
            $cant = (int) ($item['cantidad'] ?? 1);
            $monto = (float) ($item['monto'] ?? 0.00);

            // Si es híbrido, registrar el desglose en el primer item o proporcional
            $itemEf = ($idx === 0) ? $montoEfectivo : 0.00;
            $itemQr = ($idx === 0) ? $montoQr : 0.00;
            if ($metodoPago === 'efectivo') $itemEf = $monto;
            if ($metodoPago === 'qr') $itemQr = $monto;

            $stmt->execute([$pId, $prId, $cant, $monto, $metodoPago, $itemEf, $itemQr]);
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

// ── CERRAR TURNO (Dinámico: sin IDs fijos quemados) ───────────────────────────
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

        // 2. Obtener desglose dinámico de salteñas vendidas y sobrantes por variante
        $stmtDetalles = $pdo->prepare("
            SELECT s.producto_id as variante_id, s.cantidad_enviada,
                   COALESCE(SUM(v.cantidad), 0) as cantidad_vendida
            FROM stock_diario s
            LEFT JOIN ventas v ON s.producto_id = v.producto_id AND DATE(v.fecha_hora) = s.fecha
            WHERE s.fecha = ?
            GROUP BY s.producto_id, s.cantidad_enviada
        ");
        $stmtDetalles->execute([$hoy]);
        $filas = $stmtDetalles->fetchAll();
        $detallesCierre = [];

        foreach ($filas as $f) {
            $vend = (int) $f['cantidad_vendida'];
            $total = (int) $f['cantidad_enviada'];
            $sobr = max(0, $total - $vend);
            $detallesCierre[] = [
                'variante_id' => (int) $f['variante_id'],
                'cantidad_entregada' => $total,
                'cantidad_vendida_normal' => $vend,
                'cantidad_sobrante' => $sobr
            ];
        }

        if (empty($detallesCierre)) {
            $detallesCierre[] = [
                'variante_id' => 1,
                'cantidad_entregada' => $totalVendidas + $totalSobrantes,
                'cantidad_vendida_normal' => $totalVendidas,
                'cantidad_sobrante' => $totalSobrantes
            ];
        }

        // 3. Sincronización Automática con Sistema Central (sin forzar ID quemado)
        $centralApiUrl = 'https://saltenas.alloggibolivia.com/api/v1/cierres/sincronizar';
        $apiKey = 'pos_saltenas_secret_key_2026';

        $payloadCentral = [
            'fecha' => $hoy,
            'monto_real' => $dineroCobrado,
            'dinero_efectivo' => $dineroEfectivo,
            'dinero_qr' => $dineroQr,
            'observaciones' => "Efectivo: Bs. $dineroEfectivo | QR: Bs. $dineroQr | " . $observaciones,
            'detalles' => $detallesCierre
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
