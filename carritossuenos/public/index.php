<?php
require_once __DIR__ . '/db.php';

$pdo = getDBConnection();
$hoy = date('Y-m-d');

// --- AUTO-MIGRACIÓN DE TABLAS (SIN DATOS HARDCODEADOS DE PRUEBA) ---
try {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `productos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `precio` DECIMAL(10,2) NOT NULL,
        `activo` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `promociones` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `unidades` INT NOT NULL,
        `precio` DECIMAL(10,2) NOT NULL,
        `activo` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `stock_diario` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `producto_id` INT NOT NULL,
        `cantidad_enviada` INT NOT NULL DEFAULT 0,
        `aceptado` TINYINT(1) NOT NULL DEFAULT 0,
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `ventas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha_hora` DATETIME NOT NULL,
        `producto_id` INT NULL,
        `promocion_id` INT NULL,
        `cantidad` INT NOT NULL DEFAULT 1,
        `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `metodo_pago` ENUM('efectivo', 'qr', 'hibrido') NOT NULL DEFAULT 'efectivo',
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`promocion_id`) REFERENCES `promociones`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `cierres` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `total_vendidas` INT NOT NULL DEFAULT 0,
        `total_sobrantes` INT NOT NULL DEFAULT 0,
        `dinero_cobrado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `dinero_efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `dinero_qr` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `observaciones` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `turnos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `carrito_id` INT NULL,
        `fecha` DATE NOT NULL,
        `abierto_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `cerrado_at` TIMESTAMP NULL DEFAULT NULL,
        `estado` ENUM('abierto', 'cerrado') NOT NULL DEFAULT 'abierto'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `carritos_disponibles` (
        `id` INT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `zona` VARCHAR(100) NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS `despachos_recibidos` (
        `despacho_id` INT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `recibido_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    try { $pdo->exec("ALTER TABLE turnos ADD COLUMN IF NOT EXISTS carrito_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE stock_diario ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE cierres ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE turnos ADD COLUMN IF NOT EXISTS carrito_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE stock_diario ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE ventas ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE cierres ADD COLUMN IF NOT EXISTS turno_id INT NULL;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `ventas` ADD COLUMN `metodo_pago` ENUM('efectivo', 'qr') NOT NULL DEFAULT 'efectivo';"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00;"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_qr` DECIMAL(10,2) NOT NULL DEFAULT 0.00;"); } catch (Exception $e) {}
} catch (Exception $e) {}



// Limpiar productos y promociones ficticias
try {
    $pdo->exec("DELETE FROM productos WHERE nombre IN ('Pollo', 'Carne', 'Fricasé', 'Veggie') AND id NOT IN (SELECT producto_id FROM stock_diario WHERE fecha = '$hoy')");
    $pdo->exec("DELETE FROM promociones WHERE nombre LIKE 'Combo 3%' OR nombre LIKE 'Promo 5%'");
} catch (Exception $e) {}

// --- SINCRONIZACIÓN EN VIVO DEL CATÁLOGO Y PRECIOS REALES DESDE CENTRAL ---
try {
    $ch = curl_init('https://saltenas.alloggibolivia.com/api/v1/catalogo');
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

    if ($res && $httpCode === 200) {
        $json = json_decode($res, true);
        if (!empty($json['success']) && !empty($json['data'])) {
            $data = $json['data'];

            // Sincronizar lista de carritos registrados en Central
            if (!empty($data['carritos']) && is_array($data['carritos'])) {
                foreach ($data['carritos'] as $car) {
                    $stC = $pdo->prepare("INSERT INTO carritos_disponibles (id, nombre, zona) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), zona = VALUES(zona)");
                    $stC->execute([$car['id'], $car['nombre'], $car['zona'] ?? null]);
                }
            }

            // Sincronizar lista de carritos registrados en Central
            if (!empty($data['carritos']) && is_array($data['carritos'])) {
                foreach ($data['carritos'] as $car) {
                    $stC = $pdo->prepare("INSERT INTO carritos_disponibles (id, nombre, zona) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), zona = VALUES(zona)");
                    $stC->execute([$car['id'], $car['nombre'], $car['zona'] ?? null]);
                }
            }

            // 1. Sincronizar variantes con precios reales (sin defaults)
            if (!empty($data['variantes']) && is_array($data['variantes'])) {
                foreach ($data['variantes'] as $v) {
                    $nombre = $v['nombre'] ?? '';
                    $precio = (isset($v['precio_venta']) && $v['precio_venta'] !== null && $v['precio_venta'] !== '') ? (float) $v['precio_venta'] : null;
                    if (empty($nombre)) continue;

                    $stP = $pdo->prepare("SELECT id FROM productos WHERE nombre = ? LIMIT 1");
                    $stP->execute([$nombre]);
                    $prod = $stP->fetch();

                    if (!$prod) {
                        $stIns = $pdo->prepare("INSERT INTO productos (nombre, precio, activo) VALUES (?, ?, 1)");
                        $stIns->execute([$nombre, $precio]);
                    } else {
                        // Actualizar siempre el precio oficial de Central (si es null, guarda null)
                        $stUpP = $pdo->prepare("UPDATE productos SET precio = ?, activo = 1 WHERE id = ?");
                        $stUpP->execute([$precio, $prod['id']]);
                    }
                }
            }

            // 2. Sincronizar promociones oficiales
            if (!empty($data['promociones']) && is_array($data['promociones'])) {
                foreach ($data['promociones'] as $pr) {
                    $pNom = $pr['nombre'] ?? '';
                    $pUni = (int) ($pr['unidades_por_paquete'] ?? 1);
                    $pPre = (float) ($pr['precio_paquete'] ?? 0.00);
                    if (empty($pNom)) continue;

                    $stPr = $pdo->prepare("SELECT id FROM promociones WHERE nombre = ? LIMIT 1");
                    $stPr->execute([$pNom]);
                    $rowPr = $stPr->fetch();

                    if (!$rowPr) {
                        $stInsPr = $pdo->prepare("INSERT INTO promociones (nombre, unidades, precio, activo) VALUES (?, ?, ?, 1)");
                        $stInsPr->execute([$pNom, $pUni, $pPre]);
                    } else {
                        $stUpPr = $pdo->prepare("UPDATE promociones SET unidades = ?, precio = ?, activo = 1 WHERE id = ?");
                        $stUpPr->execute([$pUni, $pPre, $rowPr['id']]);
                    }
                }
            }

            // 3. Sincronizar stock del día si Central registra despacho
            if (!empty($data['despacho_hoy']) && is_array($data['despacho_hoy'])) {
                foreach ($data['despacho_hoy'] as $det) {
                    $nombre = $det['nombre'] ?? '';
                    $cant = (int) ($det['cantidad_enviada'] ?? 0);
                    if (empty($nombre)) continue;

                    $stP = $pdo->prepare("SELECT id FROM productos WHERE nombre = ? LIMIT 1");
                    $stP->execute([$nombre]);
                    $prod = $stP->fetch();
                    if (!$prod) continue;
                    $prodId = (int) $prod['id'];

                    $stS = $pdo->prepare("SELECT id, cantidad_enviada FROM stock_diario WHERE fecha = ? AND producto_id = ?");
                    $stS->execute([$hoy, $prodId]);
                    $rowS = $stS->fetch();

                    if ($rowS) {
                        if ($cant > (int)$rowS['cantidad_enviada']) {
                            $stUp = $pdo->prepare("UPDATE stock_diario SET cantidad_enviada = ? WHERE id = ?");
                            $stUp->execute([$cant, $rowS['id']]);
                        }
                    } else {
                        $stInS = $pdo->prepare("INSERT INTO stock_diario (fecha, producto_id, cantidad_enviada, aceptado) VALUES (?, ?, ?, 0)");
                        $stInS->execute([$hoy, $prodId, $cant]);
                    }
                }
            }
        }
    }
} catch (Exception $e) {}

// 1. Obtener stock del día (únicamente las salteñas despachadas)
$stmtStock = $pdo->prepare("
    SELECT s.*, p.nombre as producto_nombre, p.precio 
    FROM stock_diario s 
    JOIN productos p ON s.producto_id = p.id 
    WHERE s.fecha = ? AND s.cantidad_enviada > 0
    ORDER BY p.nombre
");
$stmtStock->execute([$hoy]);
$stockHoy = $stmtStock->fetchAll();



$stockAceptado = false;
$totalStockEnviado = 0;
foreach ($stockHoy as $st) {
    if ($st['aceptado'] == 1) {
        $stockAceptado = true;
    }
    $totalStockEnviado += (int)$st['cantidad_enviada'];
}

// 2. SOLO las salteñas recibidas en el despacho de hoy
$productos = [];
foreach ($stockHoy as $st) {
    $productos[] = [
        'id' => $st['producto_id'],
        'nombre' => $st['producto_nombre'],
        'precio' => ($st['precio'] !== null && $st['precio'] !== '') ? (float)$st['precio'] : null
    ];
}

// 3. Promociones reales (excluyendo combos ficticios de prueba)
$promociones = $pdo->query("SELECT * FROM promociones WHERE activo = 1 AND nombre NOT LIKE 'Combo 3%' AND nombre NOT LIKE 'Promo 5%' ORDER BY nombre")->fetchAll();

// 4. Control de Turno: verificar si el turno está abierto actualmente
$stmtTurno = $pdo->query("SELECT * FROM turnos WHERE estado = 'abierto' ORDER BY id DESC LIMIT 1");
$turnoActual = $stmtTurno->fetch();
$turnoAbierto = (bool) $turnoActual;

if (!$turnoAbierto) {
    // Si el turno no está abierto, la pantalla principal no muestra nada de contadores ni ventas
    $unidadesVendidasHoy = 0;
    $dineroRecaudadoHoy = 0.00;
    $dineroEfectivoHoy = 0.00;
    $dineroQrHoy = 0.00;
    $stockAceptado = false;
    $productos = [];
    $totalStockEnviado = 0;
} else {
    // Obtener ventas únicamente desde que se abrió este turno
    $stmtVentas = $pdo->prepare("
        SELECT 
            SUM(cantidad) as total_unidades, 
            SUM(monto) as total_dinero,
            SUM(CASE WHEN metodo_pago = 'efectivo' THEN monto ELSE 0 END) as dinero_efectivo,
            SUM(CASE WHEN metodo_pago = 'qr' THEN monto ELSE 0 END) as dinero_qr
        FROM ventas 
        WHERE fecha_hora >= ?
    ");
    $stmtVentas->execute([$turnoActual['abierto_at']]);
    $resVentas = $stmtVentas->fetch();
    $unidadesVendidasHoy = (int)($resVentas['total_unidades'] ?? 0);
    $dineroRecaudadoHoy = (float)($resVentas['total_dinero'] ?? 0.00);
    $dineroEfectivoHoy = (float)($resVentas['dinero_efectivo'] ?? 0.00);
    $dineroQrHoy = (float)($resVentas['dinero_qr'] ?? 0.00);
}

$cierreHoy = !$turnoAbierto;
?>
<!DOCTYPE html>
<html lang="es" class="theme-light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Carrito Sueños — POS Celular</title>
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* TEMA CLARO POR DEFECTO */
        html.theme-light {
            --bg-main: #f8fafc;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --header-bg: rgba(255, 255, 255, 0.95);
            --header-border: #e2e8f0;
            --accent-primary: #2563eb;
            --accent-success: #16a34a;
            --accent-warning: #d97706;
            --btn-product-bg: #ffffff;
            --btn-product-border: #cbd5e1;
        }

        /* TEMA OSCURO ALTERNATIVO */
        html.theme-dark {
            --bg-main: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --header-bg: rgba(30, 41, 59, 0.95);
            --header-border: #334155;
            --accent-primary: #3b82f6;
            --accent-success: #22c55e;
            --accent-warning: #fbbf24;
            --btn-product-bg: #1e293b;
            --btn-product-border: #334155;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding-bottom: 120px;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .header-counter {
            background: var(--header-bg);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--header-border);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .miniletrero-badge {
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-block;
            margin-bottom: 3px;
        }

        .miniletrero-vendidas { background: rgba(22, 163, 74, 0.12); color: #16a34a; border: 1px solid rgba(22, 163, 74, 0.25); }
        .miniletrero-restantes { background: rgba(37, 99, 235, 0.12); color: #2563eb; border: 1px solid rgba(37, 99, 235, 0.25); }
        .miniletrero-efectivo { background: rgba(217, 119, 6, 0.12); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.25); }
        .miniletrero-qr { background: rgba(147, 51, 234, 0.12); color: #9333ea; border: 1px solid rgba(147, 51, 234, 0.25); }

        .btn-touch-product {
            background: var(--btn-product-bg);
            border: 2px solid var(--btn-product-border);
            border-radius: 18px;
            padding: 18px 14px;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            text-align: left;
            width: 100%;
        }

        .btn-touch-product:active {
            transform: scale(0.94);
            border-color: var(--accent-primary);
            background: rgba(37, 99, 235, 0.08);
        }

        .btn-touch-promo {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: none;
            border-radius: 18px;
            padding: 16px;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(4, 120, 87, 0.25);
            width: 100%;
            text-align: left;
        }

        .btn-touch-promo:active {
            transform: scale(0.95);
        }

        .order-cart-card {
            background: var(--card-bg);
            border: 2px solid var(--card-border);
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        }

        .cart-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed var(--card-border);
        }

        .cart-item-row:last-child {
            border-bottom: none;
        }

        .btn-qty-control {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            background: var(--bg-main);
            color: var(--text-main);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .btn-qty-control:active {
            transform: scale(0.9);
            background: var(--accent-primary);
            color: white;
        }

        .btn-vender-main {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 4px 16px rgba(22, 163, 74, 0.35);
            transition: all 0.15s ease;
            width: 100%;
        }

        .btn-vender-main:active {
            transform: scale(0.97);
        }

        .floating-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--header-bg);
            backdrop-filter: blur(12px);
            border-top: 1px solid var(--header-border);
            padding: 12px 16px;
            z-index: 999;
        }

        /* CAMPANA DE NOTIFICACIONES ESTILO FACEBOOK */
        .fb-bell-container {
            position: relative;
        }
        .fb-bell-btn {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .fb-bell-btn:active {
            transform: scale(0.92);
        }
        .fb-bell-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ef4444;
            color: #ffffff;
            font-size: 0.7rem;
            font-weight: 800;
            min-width: 19px;
            height: 19px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--card-bg);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: bell-pulse 1.8s infinite;
        }
        @keyframes bell-pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
        .bell-shake {
            display: inline-block;
            animation: bell-shake 0.8s ease-in-out infinite alternate;
        }
        @keyframes bell-shake {
            0% { transform: rotate(-14deg); }
            100% { transform: rotate(14deg); }
        }
        .notif-dropdown-menu {
            width: 320px;
            max-width: 90vw;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.25);
            color: var(--text-main);
            padding: 0;
            overflow: hidden;
            z-index: 1050;
        }
    </style>
</head>
<body>

    <!-- CABECERA DE MONITOR CON MINILETREROS Y TOGGLE TEMA -->
    <div class="header-counter py-2 px-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-extrabold fs-6 text-gradient d-flex align-items-center" style="color: var(--text-main);">
                <i class="bi bi-shop me-2 text-warning fs-5"></i><?= htmlspecialchars($nombreCarritoActivo) ?>
            </span>
            <div class="d-flex align-items-center gap-2">
                <?php if ($turnoAbierto): ?>
                <!-- Campana de Notificaciones Estilo Facebook (Visible solo al abrir turno) -->
                <div class="dropdown fb-bell-container">
                    <button class="fb-bell-btn" type="button" id="bellNotifBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Notificaciones de Despacho">
                        <i class="bi bi-bell-fill fs-5" id="bellIcon"></i>
                        <span class="fb-bell-badge d-none" id="bellBadge">1</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu p-0 mt-2" aria-labelledby="bellNotifBtn">
                        <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--card-border) !important;">
                            <h6 class="mb-0 fw-bold d-flex align-items-center" style="color: var(--text-main);">
                                <i class="bi bi-bell me-2 text-primary"></i>Despachos Central
                            </h6>
                            <span class="badge bg-secondary-subtle text-muted fw-semibold px-2 py-1" style="font-size: 0.65rem;" id="notifStatusTag">Sin novedades</span>
                        </div>
                        <div class="p-3" id="notifDropdownContent">
                            <div class="text-center py-3 text-muted small">
                                <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                                No hay despachos registrados para hoy en Central.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Botón Cambiar Modo Claro / Oscuro -->
                <button type="button" onclick="toggleTheme()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                    <span id="theme-icon"><i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro</span>
                </button>
            </div>
        </div>

        <?php if ($turnoAbierto): ?>
        <!-- LETREROS DE CONTADORES DESTACADOS (Solo visibles una vez abierto el turno) -->
        <div class="row g-2 text-center">
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-vendidas">VENDIDAS</span>
                    <div class="fs-5 fw-extrabold text-success" id="counter-vendidas"><?= $unidadesVendidasHoy ?> u.</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-restantes">RESTANTES</span>
                    <?php $stockRestante = max(0, $totalStockEnviado - $unidadesVendidasHoy); ?>
                    <div class="fs-5 fw-extrabold text-primary" id="counter-stock"><?= $stockAceptado ? $stockRestante : 0 ?> u.</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-efectivo">EFECTIVO</span>
                    <div class="fs-6 fw-extrabold text-warning" id="counter-efectivo">Bs. <?= number_format($dineroEfectivoHoy, 0) ?></div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-qr">PAGO QR</span>
                    <div class="fs-6 fw-extrabold" style="color: #9333ea;" id="counter-qr">Bs. <?= number_format($dineroQrHoy, 0) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="container-fluid pt-3 px-3">

        <?php if (!$turnoAbierto): ?>
            <!-- PANTALLA INICIAL: TURNO CERRADO - SOLO SELECTOR Y BOTÓN ABRIR TURNO -->
            <div class="text-center py-5">
                <?php if (!empty($listaCarritos) && count($listaCarritos) > 0): ?>
                    <div class="mb-4" style="max-width: 280px; margin: 0 auto;">
                        <label class="small text-muted fw-bold mb-1.5 d-block text-center">Seleccionar Carrito:</label>
                        <select id="select-carrito" class="form-select text-center fw-extrabold rounded-pill" style="border: 2px solid var(--accent-primary); background-color: var(--card-bg); color: var(--text-main);">
                            <?php foreach ($listaCarritos as $carr): ?>
                                <option value="<?= $carr['id'] ?>">🛒 <?= htmlspecialchars($carr['nombre']) ?> <?= !empty($carr['zona']) ? '('.htmlspecialchars($carr['zona']).')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <button type="button" onclick="abrirTurno()" class="btn btn-warning btn-lg px-5 py-3 rounded-pill fw-extrabold shadow-lg text-dark">
                    <i class="bi bi-box-arrow-in-right me-2 fs-5"></i> ABRIR TURNO
                </button>
            </div>

        <?php elseif (!$stockAceptado): ?>
            <!-- PANTALLA 1: RECEPCIÓN Y ACEPTACIÓN DE STOCK DE CENTRAL -->
            <div class="text-center py-3">
                <div class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                    <i class="bi bi-box-seam me-1"></i> Stock Despachado por Central Hoy
                </div>
                <h4 class="fw-bold mb-1">Confirmar Recepción</h4>
                <p class="text-muted small mb-4">Verifica las variantes enviadas desde Administración Central:</p>

                <div class="card border-0 rounded-4 p-3 mb-4 text-start shadow-sm" style="background-color: var(--card-bg); border: 1px solid var(--card-border);">
                    <?php if (count($stockHoy) === 0): ?>
                        <div class="text-center py-3 text-muted small">
                            <i class="bi bi-hourglass-split me-1"></i> Esperando asignación de despacho desde el Sistema Central...
                        </div>
                    <?php else: ?>
                        <?php foreach ($stockHoy as $st): ?>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="fw-bold fs-6" style="color: var(--text-main);"><?= htmlspecialchars($st['producto_nombre']) ?></span>
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6"><?= $st['cantidad_enviada'] ?> unidades</span>
                            </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <span class="fw-bold text-uppercase text-muted small">Total Despachado:</span>
                            <span class="fs-5 fw-extrabold text-warning"><?= $totalStockEnviado ?> u.</span>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (count($stockHoy) > 0): ?>
                    <button type="button" onclick="aceptarStock()" class="btn btn-warning btn-lg w-100 py-3 rounded-4 fw-bold shadow-lg text-dark">
                        <i class="bi bi-check-lg me-2"></i> ACEPTAR STOCK E INICIAR VENTA
                    </button>
                <?php else: ?>
                    <button type="button" onclick="location.reload()" class="btn btn-outline-primary w-100 py-3 rounded-4 fw-bold">
                        <i class="bi bi-arrow-clockwise me-1"></i> ACTUALIZAR DESPACHO DESDE CENTRAL
                    </button>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- PANTALLA 2: VENTA RÁPIDA CON CARRITO MULTI-PRODUCTO -->

            <!-- CARRITO DEL PEDIDO ACTUAL -->
            <div class="order-cart-card">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="fw-bold text-uppercase small text-muted">
                        <i class="bi bi-cart3 me-1 text-primary"></i> Pedido Actual
                    </span>
                    <button type="button" onclick="vaciarCarrito()" class="btn btn-link text-danger text-decoration-none p-0 extra-small fw-bold" id="btn-vaciar-cart" style="display: none;">
                        <i class="bi bi-trash me-1"></i> Vaciar
                    </button>
                </div>

                <!-- LISTA DINÁMICA DE ITEMS DEL PEDIDO -->
                <div id="cart-items-container">
                    <div class="text-center py-3 text-muted small" id="cart-empty-msg">
                        <i class="bi bi-hand-index-thumb fs-4 d-block mb-1"></i>
                        Toca las salteñas de abajo para agregarlas al pedido
                    </div>
                </div>

                <!-- RESUMEN CLARO DEL PEDIDO Y SALTEÑAS -->
                <div class="pt-3 border-top mt-2 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small d-block fw-bold">TOTAL VENTA</span>
                        <span class="fs-3 fw-extrabold text-success" id="cart-total-monto">Bs. 0.00</span>
                    </div>
                    <div class="text-end">
                        <span class="text-muted extra-small d-block fw-bold">SALTEÑAS EN LA VENTA</span>
                        <span class="fs-4 fw-extrabold text-primary" id="cart-total-unidades">0 u.</span>
                    </div>
                </div>

                <!-- DESPLEGABLE DE MÉTODO DE PAGO Y BOTÓN DE VENDER -->
                <div class="mt-3 pt-2 border-top" id="checkout-section-container" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold mb-1">
                            <i class="bi bi-credit-card me-1"></i> Método de Pago:
                        </label>
                        <select id="select-metodo-pago" class="form-select form-select-lg fw-bold rounded-3" style="border: 2px solid var(--card-border); background-color: var(--bg-main); color: var(--text-main);">
                            <option value="efectivo" selected>💵 Efectivo</option>
                            <option value="qr">📱 Pago por QR</option>
                            <option value="hibrido">💸 Pago Híbrido (Efectivo + QR)</option>
                        </select>
                    </div>

                    <div id="hibrido-fields" style="display:none;" class="mb-3">

                        <div class="row g-2">

                            <div class="col-6">

                                <label class="small text-muted fw-bold">En Efectivo (Bs.)</label>

                                <input type="number" id="hibrido-efectivo" class="form-control" step="0.50" min="0" placeholder="0.00">

                            </div>

                            <div class="col-6">

                                <label class="small text-muted fw-bold">En QR (Bs.)</label>

                                <input type="number" id="hibrido-qr" class="form-control" step="0.50" min="0" placeholder="0.00">

                            </div>

                        </div>

                    </div>

                    <button type="button" onclick="procesarCobroOrden()" class="btn-vender-main">
                        <i class="bi bi-check2-circle me-1"></i> CONFIRMAR Y REGISTRAR VENTA
                    </button>
                </div>
            </div>


            <div class="row g-3 mb-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="col-6">
                        <button type="button" onclick="agregarAlCarrito('individual', <?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio'] ?>, 1)" class="btn-touch-product">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-extrabold fs-5" style="color: var(--text-main);"><?= htmlspecialchars($prod['nombre']) ?></span>
                                <?php if ($prod['precio'] !== null): ?>
                                    <span class="badge bg-warning text-dark rounded-pill fw-extrabold px-2 py-1">Bs. <?= number_format($prod['precio'], 2) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted extra-small fw-semibold">+ Añadir a la Venta</div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PROMOCIONES DINÁMICAS DESDE CENTRAL -->
            <?php if (count($promociones) > 0): ?>
                <div class="mb-2">
                    <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-stars me-1 text-warning"></i> Promociones de Central</span>
                </div>
                <div class="row g-3 mb-4">
                    <?php foreach ($promociones as $promo): ?>
                        <div class="col-12">
                            <button type="button" onclick="agregarAlCarrito('promocion', <?= $promo['id'] ?>, '<?= addslashes($promo['nombre']) ?>', <?= $promo['precio'] ?>, <?= $promo['unidades'] ?>)" class="btn-touch-promo">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-extrabold fs-6 d-block text-warning mb-1"><i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($promo['nombre']) ?></span>
                                        <span class="text-white opacity-90 small">Descuenta <?= $promo['unidades'] ?> salteñas del stock</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-extrabold text-white">Bs. <?= number_format($promo['precio'], 0) ?></span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <?php if ($turnoAbierto && $stockAceptado): ?>
        <!-- BOTÓN FIJO INFERIOR: CERRAR TURNO -->
        <div class="floating-footer">
            <button type="button" class="btn btn-outline-danger w-100 py-3 rounded-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalCierre">
                <i class="bi bi-door-closed-fill me-2"></i> CERRAR TURNO DEL DÍA
            </button>
        </div>
    <?php endif; ?>

    <!-- MODAL DE CIERRE DE TURNO -->
    <div class="modal fade" id="modalCierre" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4" style="background-color: var(--card-bg); border: 1px solid var(--card-border);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i class="bi bi-clipboard-check text-warning me-2"></i>Cierre de Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Revisa el resumen final de tu jornada antes de enviar a Central:</p>

                    <div class="p-3 rounded-3 mb-3" style="background: rgba(0,0,0,0.04); border: 1px solid var(--card-border);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Salteñas Vendidas:</span>
                            <span class="fw-bold text-success" id="modal-total-vendidas"><?= $unidadesVendidasHoy ?> u.</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Stock Sobrante Estimado:</span>
                            <span class="fw-bold text-primary" id="modal-total-sobrantes"><?= $stockRestante ?> u.</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Recaudado en Efectivo:</span>
                            <span class="fw-bold text-warning" id="modal-dinero-efectivo">Bs. <?= number_format($dineroEfectivoHoy, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Recaudado en QR:</span>
                            <span class="fw-bold" style="color: #9333ea;" id="modal-dinero-qr">Bs. <?= number_format($dineroQrHoy, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold text-dark">Total Dinero General:</span>
                            <span class="fw-bold text-dark fs-5" id="modal-total-dinero">Bs. <?= number_format($dineroRecaudadoHoy, 2) ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Salteñas Dañadas / Rotas u Observaciones:</label>
                        <textarea id="observaciones-cierre" rows="3" class="form-control rounded-3" placeholder="Ej: Se rompió 1 salteña de pollo al transportar..."></textarea>
                    </div>

                    <button type="button" onclick="confirmarCierreTurno()" class="btn btn-warning w-100 py-3 rounded-3 fw-bold text-dark">
                        <i class="bi bi-send-check-fill me-1"></i> CONFIRMAR Y FINALIZAR TURNO
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let unidadesVendidas = <?= $unidadesVendidasHoy ?>;
        let dineroEfectivo = <?= $dineroEfectivoHoy ?>;
        let dineroQr = <?= $dineroQrHoy ?>;
        let totalStockEnviado = <?= $totalStockEnviado ?>;

        let carrito = [];

        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            if (html.classList.contains('theme-light')) {
                html.classList.remove('theme-light');
                html.classList.add('theme-dark');
                icon.innerHTML = '<i class="bi bi-sun-fill me-1 text-warning"></i> Modo Claro';
            } else {
                html.classList.remove('theme-dark');
                html.classList.add('theme-light');
                icon.innerHTML = '<i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro';
            }
        }

        function agregarAlCarrito(tipo, id, nombre, precio, unidadesPorPack) {
            if (navigator.vibrate) navigator.vibrate(35);

            let key = tipo + '_' + id;
            let itemExistente = carrito.find(i => i.key === key);

            if (itemExistente) {
                itemExistente.cantidad += 1;
            } else {
                carrito.push({
                    key: key,
                    tipo: tipo,
                    id: id,
                    nombre: nombre,
                    precio: parseFloat(precio),
                    unidadesPorPack: parseInt(unidadesPorPack),
                    cantidad: 1
                });
            }

            renderizarCarrito();
        }

        function cambiarCantidadItem(key, delta) {
            let item = carrito.find(i => i.key === key);
            if (item) {
                item.cantidad += delta;
                if (item.cantidad <= 0) {
                    carrito = carrito.filter(i => i.key !== key);
                }
            }
            renderizarCarrito();
        }

        function vaciarCarrito() {
            carrito = [];
            renderizarCarrito();
        }

        function renderizarCarrito() {
            const container = document.getElementById('cart-items-container');
            const btnVaciar = document.getElementById('btn-vaciar-cart');
            const checkoutSection = document.getElementById('checkout-section-container');

            if (carrito.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted small" id="cart-empty-msg"><i class="bi bi-hand-index-thumb fs-4 d-block mb-1"></i>Toca las salteñas de abajo para agregarlas al pedido</div>';
                btnVaciar.style.display = 'none';
                checkoutSection.style.display = 'none';
                document.getElementById('cart-total-monto').innerText = 'Bs. 0.00';
                document.getElementById('cart-total-unidades').innerText = '0 u.';
                return;
            }

            btnVaciar.style.display = 'inline-block';
            checkoutSection.style.display = 'block';

            let html = '';
            let totalMonto = 0;
            let totalUnidades = 0;

            carrito.forEach(item => {
                let subtotal = item.precio * item.cantidad;
                let unidadesItem = item.unidadesPorPack * item.cantidad;
                totalMonto += subtotal;
                totalUnidades += unidadesItem;

                html += `
                    <div class="cart-item-row">
                        <div>
                            <span class="fw-extrabold d-block" style="color: var(--text-main); font-size: 0.95rem;">${item.nombre}</span>
                            <span class="text-muted extra-small">Bs. ${item.precio.toFixed(2)} c/u</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-qty-control" onclick="cambiarCantidadItem('${item.key}', -1)">-</button>
                            <span class="fw-extrabold px-1 fs-6" style="color: var(--text-main);">${item.cantidad}</span>
                            <button type="button" class="btn-qty-control" onclick="cambiarCantidadItem('${item.key}', 1)">+</button>
                            <span class="fw-bold text-success ms-2" style="min-width: 60px; text-align: right;">Bs. ${subtotal.toFixed(2)}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            document.getElementById('cart-total-monto').innerText = 'Bs. ' + totalMonto.toFixed(2);
            document.getElementById('cart-total-unidades').innerText = totalUnidades + ' u.';
        }

        function procesarCobroOrden() {
            if (carrito.length === 0) return;
            if (navigator.vibrate) navigator.vibrate(60);

            const metodo = document.getElementById('select-metodo-pago').value;
            let payloadItems = [];
            let totalCobrado = 0;
            let totalSalteñasVendidas = 0;

            carrito.forEach(i => {
                let subtotal = i.precio * i.cantidad;
                let uCount = i.unidadesPorPack * i.cantidad;
                totalCobrado += subtotal;
                totalSalteñasVendidas += uCount;

                payloadItems.push({
                    tipo: i.tipo,
                    producto_id: i.tipo === 'individual' ? i.id : null,
                    promocion_id: i.tipo === 'promocion' ? i.id : null,
                    cantidad: uCount,
                    monto: subtotal
                });
            });

            let montoEfectivo = 0, montoQr = 0;
            if (metodo === 'efectivo') {
                montoEfectivo = totalCobrado;
            } else if (metodo === 'qr') {
                montoQr = totalCobrado;
            } else if (metodo === 'hibrido') {
                montoEfectivo = parseFloat(document.getElementById('hibrido-efectivo').value || 0);
                montoQr = parseFloat(document.getElementById('hibrido-qr').value || 0);
            }

            unidadesVendidas += totalSalteñasVendidas;
            dineroEfectivo += montoEfectivo;
            dineroQr += montoQr;
            actualizarIndicadores();

            vaciarCarrito();
            // Ocultar campos híbridos y resetear
            document.getElementById('hibrido-fields').style.display = 'none';
            document.getElementById('hibrido-efectivo').value = '';
            document.getElementById('hibrido-qr').value = '';

            fetch('api.php?accion=registrar_venta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: payloadItems,
                    metodo_pago: metodo,
                    monto_efectivo: montoEfectivo,
                    monto_qr: montoQr
                })
            });
        }

        function actualizarIndicadores() {
            let totalDinero = dineroEfectivo + dineroQr;
            document.getElementById('counter-vendidas').innerText = unidadesVendidas + ' u.';
            document.getElementById('counter-efectivo').innerText = 'Bs. ' + dineroEfectivo.toFixed(0);
            document.getElementById('counter-qr').innerText = 'Bs. ' + dineroQr.toFixed(0);
            let sobrante = Math.max(0, totalStockEnviado - unidadesVendidas);
            document.getElementById('counter-stock').innerText = sobrante + ' u.';

            document.getElementById('modal-total-vendidas').innerText = unidadesVendidas + ' u.';
            document.getElementById('modal-total-sobrantes').innerText = sobrante + ' u.';
            document.getElementById('modal-dinero-efectivo').innerText = 'Bs. ' + dineroEfectivo.toFixed(2);
            document.getElementById('modal-dinero-qr').innerText = 'Bs. ' + dineroQr.toFixed(2);
            document.getElementById('modal-total-dinero').innerText = 'Bs. ' + totalDinero.toFixed(2);
        }

        function aceptarStock() {
            fetch('api.php?accion=aceptar_stock')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
        }

        function confirmarCierreTurno() {
            let obs = document.getElementById('observaciones-cierre').value;
            let sobrantes = Math.max(0, totalStockEnviado - unidadesVendidas);
            let totalDinero = dineroEfectivo + dineroQr;

            fetch('api.php?accion=cerrar_turno', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    total_vendidas: unidadesVendidas,
                    total_sobrantes: sobrantes,
                    dinero_cobrado: totalDinero,
                    dinero_efectivo: dineroEfectivo,
                    dinero_qr: dineroQr,
                    observaciones: obs
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al cerrar turno: ' + data.error);
                }
            });
        }

        // Mostrar/ocultar campos de pago híbrido

        document.getElementById('select-metodo-pago').addEventListener('change', function() {

            document.getElementById('hibrido-fields').style.display = this.value === 'hibrido' ? 'block' : 'none';

        });



        // ── CAMPANA DE NOTIFICACIONES ESTILO FACEBOOK ─────────────────────────
        let ultimoDespachoData = null;

        function verificarDespachoCentral() {
            fetch('api.php?accion=verificar_despacho')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    const bellBadge = document.getElementById('bellBadge');
                    const bellIcon = document.getElementById('bellIcon');
                    const notifStatusTag = document.getElementById('notifStatusTag');
                    const notifContent = document.getElementById('notifDropdownContent');

                    if (data.hay_despacho && !data.aceptado) {
                        ultimoDespachoData = data.despacho;
                        // Activar campana estilo Facebook
                        if (bellBadge) {
                            bellBadge.classList.remove('d-none');
                            bellBadge.innerText = '1';
                        }
                        if (bellIcon) {
                            bellIcon.classList.add('bell-shake', 'text-warning');
                        }
                        if (notifStatusTag) {
                            notifStatusTag.className = 'badge bg-danger text-white fw-bold px-2 py-1';
                            notifStatusTag.innerText = '¡Nuevo Despacho!';
                        }

                        let detallesHtml = '';
                        let totalUnidadesRecibidas = 0;
                        if (data.despacho && data.despacho.detalles) {
                            data.despacho.detalles.forEach(d => {
                                let cant = parseInt(d.cantidad_enviada || 0);
                                totalUnidadesRecibidas += cant;
                                detallesHtml += `
                                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light">
                                        <span class="small fw-semibold"><i class="bi bi-box-seam me-1 text-primary"></i>${d.variante_nombre || 'Salteña'}</span>
                                        <span class="badge bg-secondary-subtle text-dark fw-bold">${cant} u.</span>
                                    </div>
                                `;
                            });
                        }

                        if (notifContent) {
                            notifContent.innerHTML = `
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-warning text-dark fw-extrabold px-2 py-1">EN CAMINO</span>
                                        <small class="text-muted">${data.despacho.fecha || 'Hoy'}</small>
                                    </div>
                                    <p class="small fw-bold mb-2" style="color: var(--text-main);">
                                        📦 Central ha despachado <span class="text-primary">${totalUnidadesRecibidas} salteñas</span>:
                                    </p>
                                    <div class="bg-body-secondary p-2 rounded-3 mb-3">
                                        ${detallesHtml || '<div class="small text-muted">Detalles en proceso...</div>'}
                                    </div>
                                    <button type="button" onclick="aceptarDespachoRecibido()" class="btn btn-success btn-sm w-100 fw-bold py-2 rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-check-circle-fill"></i> Aceptar y Recibir Stock
                                    </button>
                                </div>
                            `;
                        }
                    } else if (data.hay_despacho && data.aceptado) {
                        if (bellBadge) bellBadge.classList.add('d-none');
                        if (bellIcon) bellIcon.classList.remove('bell-shake', 'text-warning');
                        if (notifStatusTag) {
                            notifStatusTag.className = 'badge bg-success-subtle text-success fw-semibold px-2 py-1';
                            notifStatusTag.innerText = 'Recibido';
                        }
                        if (notifContent) {
                            notifContent.innerHTML = `
                                <div class="text-center py-2">
                                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                                    <div class="small fw-bold text-success">Despacho de hoy aceptado</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">El stock ya está listo y habilitado para ventas.</div>
                                </div>
                            `;
                        }
                    } else {
                        if (bellBadge) bellBadge.classList.add('d-none');
                        if (bellIcon) bellIcon.classList.remove('bell-shake', 'text-warning');
                        if (notifStatusTag) {
                            notifStatusTag.className = 'badge bg-secondary-subtle text-muted fw-semibold px-2 py-1';
                            notifStatusTag.innerText = 'Sin novedades';
                        }
                        if (notifContent) {
                            notifContent.innerHTML = `
                                <div class="text-center py-3 text-muted small">
                                    <i class="bi bi-inbox fs-2 d-block mb-1 opacity-50"></i>
                                    No hay despachos registrados para hoy en Central.
                                </div>
                            `;
                        }
                    }
                })
                .catch(() => {});
        }

        function aceptarDespachoRecibido() {
            if (navigator.vibrate) navigator.vibrate([40, 60, 40]);
            const payload = {
                despacho_id: ultimoDespachoData ? ultimoDespachoData.id : null,
                detalles: ultimoDespachoData ? ultimoDespachoData.detalles : []
            };

            fetch('api.php?accion=aceptar_stock', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo aceptar el stock'));
                }
            })
            .catch(() => alert('Error de red al aceptar el stock.'));
        }

        function abrirTurno() {
            if (navigator.vibrate) navigator.vibrate(50);
            const sel = document.getElementById('select-carrito');
            const carritoId = sel ? sel.value : null;

            fetch('api.php?accion=abrir_turno', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ carrito_id: carritoId })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error al abrir turno: ' + (data.error || ''));
                }
            })
            .catch(() => alert('Error de red al abrir turno'));
        }

        <?php if ($turnoAbierto): ?>
        // Consultar despacho de inmediato y luego cada 15 segundos (solo con turno abierto)
        verificarDespachoCentral();
        setInterval(verificarDespachoCentral, 15000);
        <?php endif; ?>
    </script>
</body>
</html>