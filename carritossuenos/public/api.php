<?php

header('Content-Type: application/json');
require_once __DIR__ . '/db.php';

$pdo = getDBConnection();
$accion = $_REQUEST['accion'] ?? '';
$hoy = date('Y-m-d');

// Soporte de tablas y columnas necesarias
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `turnos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `carrito_id` INT NULL DEFAULT 1,
        `fecha` DATE NOT NULL,
        `abierto_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `cerrado_at` TIMESTAMP NULL DEFAULT NULL,
        `estado` ENUM('abierto', 'cerrado') NOT NULL DEFAULT 'abierto'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `despachos_recibidos` (
        `despacho_id` INT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `recibido_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    try { $pdo->exec("ALTER TABLE turnos ADD COLUMN IF NOT EXISTS carrito_id INT NULL DEFAULT 1;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE stock_diario ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE cierres ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas MODIFY COLUMN metodo_pago VARCHAR(20) NOT NULL DEFAULT 'efectivo'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS monto_efectivo DECIMAL(10,2) NOT NULL DEFAULT 0.00"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS monto_qr DECIMAL(10,2) NOT NULL DEFAULT 0.00"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE promociones ADD COLUMN IF NOT EXISTS variante_id INT NOT NULL DEFAULT 1;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE promociones ADD COLUMN IF NOT EXISTS variante_nombre VARCHAR(100) NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE despachos_recibidos ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE cierres ADD COLUMN IF NOT EXISTS sincronizado_central TINYINT(1) NOT NULL DEFAULT 1;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE cierres ADD COLUMN IF NOT EXISTS payload_central MEDIUMTEXT NULL;"); } catch (Exception $e) {}
} catch (Exception $e) {}

// ── ABRIR TURNO (Directo a Carrito 1) ─────────────────────────────────────────
if ($accion === 'abrir_turno') {
    try {
        // Cerrar cualquier turno abierto anterior
        $pdo->exec("UPDATE turnos SET estado = 'cerrado', cerrado_at = NOW() WHERE estado = 'abierto'");

        // Abrir nuevo turno limpio para Carrito 1
        $st = $pdo->prepare("INSERT INTO turnos (carrito_id, fecha, estado, abierto_at) VALUES (1, ?, 'abierto', NOW())");
        $st->execute([$hoy]);
        $nuevoTurnoId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'mensaje' => 'Turno abierto con éxito',
            'turno_id' => $nuevoTurnoId
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── VERIFICAR DESPACHO DESDE CENTRAL ──────────────────────────────────────────
if ($accion === 'verificar_despacho') {
    try {
        // Resolver turno actual
        $stTurnoActual = $pdo->query("SELECT id FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1");
        $turnoActualRow = $stTurnoActual->fetch();
        if (!$turnoActualRow) {
            echo json_encode(['success' => false, 'error' => 'No hay turno abierto']);
            exit;
        }

        $curTurnoId = (int)$turnoActualRow['id'];

        // Estado local de stock_diario para ESTE turno
        $stmtStk = $pdo->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN aceptado = 1 THEN 1 ELSE 0 END) as aceptados FROM stock_diario WHERE turno_id = ?");
        $stmtStk->execute([$curTurnoId]);
        $stkLocal = $stmtStk->fetch();
        $hayStockLocal = ($stkLocal['total'] ?? 0) > 0;
        $yaAceptadoLocal = $hayStockLocal && (($stkLocal['aceptados'] ?? 0) > 0);

        // Consultar Salteñas Central para Carrito 1
        $urlDespacho = "https://saltenas.alloggibolivia.com/api/v1/despachos/hoy?carrito_id=1";
        $ch = curl_init($urlDespacho);
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
                $stCheck = $pdo->prepare("SELECT despacho_id FROM despachos_recibidos WHERE despacho_id = ? AND turno_id = ?");
                $stCheck->execute([$despachoId, $curTurnoId]);
                $yaAceptadoLocalmente = (bool) $stCheck->fetch();
            }
            // Aceptado en este turno solo si ya fue registrado localmente en este turno_id
            $esAceptado = $yaAceptadoLocalmente;

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
                    'estado' => $yaAceptadoLocal ? 'aceptado' : 'pendiente',
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
        $stTurnoActual = $pdo->query("SELECT id FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1");
        $turnoActualRow = $stTurnoActual->fetch();
        if (!$turnoActualRow) {
            echo json_encode(['success' => false, 'error' => 'No hay turno abierto para aceptar stock']);
            exit;
        }

        $curTurnoId = (int)$turnoActualRow['id'];

        $pdo->beginTransaction();

        // Si no vienen detalles por POST, obtenerlos de Central para Carrito 1
        if (empty($detalles)) {
            $urlDespacho = "https://saltenas.alloggibolivia.com/api/v1/despachos/hoy?carrito_id=1";
            $ch = curl_init($urlDespacho);
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

        // 1. Verificar si este despacho ya fue procesado localmente en este turno
        $yaProcesado = false;
        if ($despachoId) {
            $stCheck = $pdo->prepare("SELECT despacho_id FROM despachos_recibidos WHERE despacho_id = ? AND turno_id = ?");
            $stCheck->execute([$despachoId, $curTurnoId]);
            if ($stCheck->fetch()) {
                $yaProcesado = true;
            }
        }

        // 2. Si no ha sido procesado, procesar detalles y registrar en stock_diario para este turno
        if (!$yaProcesado && !empty($detalles) && is_array($detalles)) {
            foreach ($detalles as $det) {
                $nombre = $det['variante_nombre'] ?? ($det['nombre'] ?? '');
                $cant = (int) ($det['cantidad_enviada'] ?? 0);
                $precio = (isset($det['precio']) && $det['precio'] !== null && $det['precio'] !== '') ? (float) $det['precio'] : null;
                if (empty($nombre)) continue;

                $varId = (int) ($det['variante_id'] ?? ($det['id'] ?? 0));
                if ($varId <= 0) continue;

                // Guardar/actualizar producto con el ID oficial exacto de Central
                $stP = $pdo->prepare("
                    INSERT INTO productos (id, nombre, precio, activo) 
                    VALUES (?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), precio = VALUES(precio), activo = 1
                ");
                $stP->execute([$varId, $nombre, $precio]);

                // Guardar stock asignado exclusivamente a este turno_id usando el variante_id exacto
                $stS = $pdo->prepare("SELECT id, cantidad_enviada FROM stock_diario WHERE turno_id = ? AND producto_id = ?");
                $stS->execute([$curTurnoId, $varId]);
                $rowStock = $stS->fetch();

                if ($rowStock) {
                    $stUp = $pdo->prepare("UPDATE stock_diario SET cantidad_enviada = cantidad_enviada + ?, aceptado = 1 WHERE id = ?");
                    $stUp->execute([$cant, $rowStock['id']]);
                } else {
                    $stInS = $pdo->prepare("INSERT INTO stock_diario (turno_id, fecha, producto_id, cantidad_enviada, aceptado) VALUES (?, ?, ?, ?, 1)");
                    $stInS->execute([$curTurnoId, $hoy, $varId, $cant]);
                }
            }

            if ($despachoId) {
                try {
                    $stRec = $pdo->prepare("INSERT INTO despachos_recibidos (despacho_id, turno_id, fecha) VALUES (?, ?, ?)");
                    $stRec->execute([$despachoId, $curTurnoId, $hoy]);
                } catch (Exception $eRec) {
                    $stRec = $pdo->prepare("INSERT IGNORE INTO despachos_recibidos (despacho_id, fecha) VALUES (?, ?)");
                    $stRec->execute([$despachoId, $hoy]);
                }
            }
        }

        // Marcar el stock de este turno como aceptado
        $stmt = $pdo->prepare("UPDATE stock_diario SET aceptado = 1 WHERE turno_id = ?");
        $stmt->execute([$curTurnoId]);

        $pdo->commit();

        // Notificar a Salteñas Central el despacho aceptado
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
            curl_exec($ch);
            curl_close($ch);
        }

        echo json_encode([
            'success' => true,
            'message' => '¡Stock aceptado y recibido con éxito!'
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── REGISTRAR VENTA ──────────────────────────────────────────────────────────
if ($accion === 'registrar_venta') {
    $input = json_decode(file_get_contents('php://input'), true);

    $items = $input['items'] ?? [];
    $metodoPago = $input['metodo_pago'] ?? 'efectivo';
    $montoEfectivo = (float) ($input['monto_efectivo'] ?? 0.00);
    $montoQr = (float) ($input['monto_qr'] ?? 0.00);

    if (empty($items) || !is_array($items)) {
        echo json_encode(['success' => false, 'error' => 'No hay productos en el pedido']);
        exit;
    }

    try {
        $stTurnoActual = $pdo->query("SELECT id FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1");
        $tRow = $stTurnoActual->fetch();
        if (!$tRow) {
            echo json_encode(['success' => false, 'error' => 'No hay ningún turno abierto para registrar ventas']);
            exit;
        }
        $activoTurnoId = (int)$tRow['id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("
            INSERT INTO ventas (turno_id, fecha_hora, producto_id, promocion_id, cantidad, monto, metodo_pago, monto_efectivo, monto_qr) 
            VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)
        ");

        $totalItemsMonto = 0;
        foreach ($items as $item) {
            $totalItemsMonto += (float) ($item['monto'] ?? 0);
        }

        foreach ($items as $item) {
            $pId = !empty($item['producto_id']) ? (int) $item['producto_id'] : null;
            $prId = !empty($item['promocion_id']) ? (int) $item['promocion_id'] : null;
            $cant = (int) ($item['cantidad'] ?? 1);
            $monto = (float) ($item['monto'] ?? 0.00);

            // Si es promoción y no vino producto_id, resolver su variante_id directamente de promociones
            if (!$pId && $prId) {
                $stPrV = $pdo->prepare("SELECT variante_id FROM promociones WHERE id = ?");
                $stPrV->execute([$prId]);
                $pId = (int)$stPrV->fetchColumn() ?: null;
            }

            $itemEf = 0.00;
            $itemQr = 0.00;
            if ($metodoPago === 'efectivo') {
                $itemEf = $monto;
            } elseif ($metodoPago === 'qr') {
                $itemQr = $monto;
            } elseif ($metodoPago === 'hibrido') {
                $prop = ($totalItemsMonto > 0) ? ($monto / $totalItemsMonto) : 0;
                $itemEf = round($montoEfectivo * $prop, 2);
                $itemQr = round($montoQr * $prop, 2);
            }

            $stmt->execute([$activoTurnoId, $pId, $prId, $cant, $monto, $metodoPago, $itemEf, $itemQr]);
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

// ── CERRAR TURNO ─────────────────────────────────────────────────────────────
if ($accion === 'cerrar_turno') {
    $input = json_decode(file_get_contents('php://input'), true);
    $totalVendidas = (int) ($input['total_vendidas'] ?? 0);
    $totalSobrantes = (int) ($input['total_sobrantes'] ?? 0);
    $dineroEfectivo = (float) ($input['dinero_efectivo'] ?? 0.00);
    $dineroQr = (float) ($input['dinero_qr'] ?? 0.00);
    $dineroCobrado = $dineroEfectivo + $dineroQr;
    $observaciones = trim($input['observaciones'] ?? '');

    try {
        $pdo->beginTransaction();

        $stTCierre = $pdo->query("SELECT id FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1 FOR UPDATE");
        $tCierreRow = $stTCierre->fetch();
        if (!$tCierreRow) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Este turno ya fue cerrado o no existe un turno abierto.']);
            exit;
        }
        $closingTurnoId = (int)$tCierreRow['id'];

        // Marcar turno como cerrado de forma atómica (impide cierres duplicados)
        $stUpdTurno = $pdo->prepare("UPDATE turnos SET estado = 'cerrado', cerrado_at = NOW() WHERE id = ? AND estado = 'abierto'");
        $stUpdTurno->execute([$closingTurnoId]);
        if ($stUpdTurno->rowCount() === 0) {
            $pdo->rollBack();
            echo json_encode(['success' => false, 'error' => 'Este turno ya fue cerrado.']);
            exit;
        }

        // Guardado Local definitivo del cierre
        $stmt = $pdo->prepare("INSERT INTO cierres (turno_id, fecha, total_vendidas, total_sobrantes, dinero_cobrado, dinero_efectivo, dinero_qr, observaciones, sincronizado_central) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$closingTurnoId, $hoy, $totalVendidas, $totalSobrantes, $dineroCobrado, $dineroEfectivo, $dineroQr, $observaciones]);
        $closingCierreId = (int)$pdo->lastInsertId();

        $pdo->commit();

        // Desglose de salteñas vendidas y sobrantes del turno cerrado
        $detallesInput = $input['detalles'] ?? [];
        $detallesCierre = [];

        if (!empty($detallesInput) && is_array($detallesInput)) {
            foreach ($detallesInput as $di) {
                $detallesCierre[] = [
                    'variante_id' => (int) ($di['id'] ?? 0),
                    'cantidad_entregada' => (int) ($di['enviada'] ?? ($di['cantidad_entregada'] ?? 0)),
                    'cantidad_vendida_normal' => (int) ($di['vendida'] ?? ($di['cantidad_vendida_normal'] ?? 0)),
                    'cantidad_sobrante' => (int) ($di['restante'] ?? ($di['cantidad_sobrante'] ?? 0))
                ];
            }
        } else {
            $stmtDetalles = $pdo->prepare("
                SELECT s.producto_id as variante_id, s.cantidad_enviada,
                       COALESCE(SUM(v.cantidad), 0) as cantidad_vendida
                FROM stock_diario s
                LEFT JOIN ventas v ON s.producto_id = v.producto_id AND v.turno_id = s.turno_id
                WHERE s.turno_id = ?
                GROUP BY s.producto_id, s.cantidad_enviada
            ");
            $stmtDetalles->execute([$closingTurnoId]);
            $filas = $stmtDetalles->fetchAll();

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
        }

        // Sincronización Automática con Sistema Central (Carrito 1)
        $centralApiUrl = 'https://saltenas.alloggibolivia.com/api/v1/cierres/sincronizar';
        $apiKey = 'pos_saltenas_secret_key_2026';

        $payloadCentral = [
            'carrito_id' => 1,
            'fecha' => $hoy,
            'monto_real' => $dineroCobrado,
            'dinero_efectivo' => $dineroEfectivo,
            'dinero_qr' => $dineroQr,
            'total_vendidas' => $totalVendidas,
            'total_sobrantes' => $totalSobrantes,
            'observaciones' => "Efectivo: Bs. $dineroEfectivo | QR: Bs. $dineroQr | " . $observaciones,
            'detalles' => $detallesCierre
        ];
        $payloadJson = json_encode($payloadCentral);

        try {
            $pdo->prepare("UPDATE cierres SET payload_central = ? WHERE id = ?")->execute([$payloadJson, $closingCierreId]);
        } catch (Exception $ePay) {}

        $ch = curl_init($centralApiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-POS-Api-Key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
        $resCentral = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCodeCentral = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $jsonCentral = $resCentral ? json_decode($resCentral, true) : null;
        $sincronizadoOk = (!$curlErr && $httpCodeCentral >= 200 && $httpCodeCentral < 300 && !empty($jsonCentral['success']));

        if ($sincronizadoOk) {
            try {
                $pdo->prepare("UPDATE cierres SET sincronizado_central = 1 WHERE id = ?")->execute([$closingCierreId]);
            } catch (Exception $eUp) {}
            echo json_encode([
                'success' => true,
                'sincronizado_central' => true,
                'message' => 'Cierre de turno completado y sincronizado con Central'
            ]);
        } else {
            $syncErr = $curlErr ?: ($jsonCentral['error'] ?? $jsonCentral['message'] ?? ("HTTP " . $httpCodeCentral));
            echo json_encode([
                'success' => true,
                'sincronizado_central' => false,
                'central_http' => $httpCodeCentral,
                'sync_error' => $syncErr,
                'message' => 'Turno cerrado localmente, pendiente de sincronizar con Central.'
            ]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// ── REINTENTAR ENVÍO DE CIERRES PENDIENTES DE SINCRONIZAR CON CENTRAL ────────
if ($accion === 'reintentar_cierres') {
    try {
        $pendientes = $pdo->query("SELECT id, payload_central FROM cierres WHERE sincronizado_central = 0 AND payload_central IS NOT NULL ORDER BY id ASC")->fetchAll();
        $enviados = 0;
        $ultimoError = null;
        $centralApiUrl = 'https://saltenas.alloggibolivia.com/api/v1/cierres/sincronizar';
        $apiKey = 'pos_saltenas_secret_key_2026';

        foreach ($pendientes as $p) {
            $ch = curl_init($centralApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $p['payload_central']);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-POS-Api-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            $resCentral = curl_exec($ch);
            $curlErr = curl_error($ch);
            $httpCodeCentral = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $jsonCentral = $resCentral ? json_decode($resCentral, true) : null;
            if (!$curlErr && $httpCodeCentral >= 200 && $httpCodeCentral < 300 && !empty($jsonCentral['success'])) {
                $pdo->prepare("UPDATE cierres SET sincronizado_central = 1 WHERE id = ?")->execute([$p['id']]);
                $enviados++;
            } else {
                $ultimoError = $curlErr ?: ($jsonCentral['error'] ?? $jsonCentral['message'] ?? ("HTTP " . $httpCodeCentral));
            }
        }

        $restantes = (int) $pdo->query("SELECT COUNT(*) FROM cierres WHERE sincronizado_central = 0 AND payload_central IS NOT NULL")->fetchColumn();
        echo json_encode([
            'success' => $restantes === 0,
            'enviados' => $enviados,
            'restantes' => $restantes,
            'error' => $ultimoError
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida']);
