<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$hoy = date('Y-m-d');

// --- AUTO-MIGRACIÓN TRANSPARENTE DE BASE DE DATOS EN PRODUCCIÓN ---
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
        `metodo_pago` ENUM('efectivo', 'qr') NOT NULL DEFAULT 'efectivo',
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
    ");

    try {
        $pdo->exec("ALTER TABLE `ventas` ADD COLUMN `metodo_pago` ENUM('efectivo', 'qr') NOT NULL DEFAULT 'efectivo';");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00;");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_qr` DECIMAL(10,2) NOT NULL DEFAULT 0.00;");
    } catch (Exception $e) {
    }

    $countProd = $pdo->query("SELECT COUNT(*) FROM `productos`")->fetchColumn();
    if ($countProd == 0) {
        $pdo->exec("
        INSERT INTO `productos` (`nombre`, `precio`) VALUES
        ('Pollo', 10.00),
        ('Carne', 10.00),
        ('Fricasé', 11.00),
        ('Veggie', 10.00);
        ");
    }

    $countPromo = $pdo->query("SELECT COUNT(*) FROM `promociones`")->fetchColumn();
    if ($countPromo == 0) {
        $pdo->exec("
        INSERT INTO `promociones` (`nombre`, `unidades`, `precio`) VALUES
        ('Combo 3 Salteñas x 25 Bs', 3, 25.00),
        ('Promo 5 Salteñas x 40 Bs', 5, 40.00);
        ");
    }

    $countStock = $pdo->query("SELECT COUNT(*) FROM `stock_diario` WHERE `fecha` = '$hoy'")->fetchColumn();
    if ($countStock == 0) {
        $pdo->exec("
        INSERT INTO `stock_diario` (`fecha`, `producto_id`, `cantidad_enviada`, `aceptado`) VALUES
        ('$hoy', 1, 40, 0),
        ('$hoy', 2, 30, 0),
        ('$hoy', 3, 15, 0);
        ");
    }
} catch (Exception $e) {
}

// 1. Obtener productos
$productos = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY nombre")->fetchAll();

// 2. Obtener promociones
$promociones = $pdo->query("SELECT * FROM promociones WHERE activo = 1 ORDER BY nombre")->fetchAll();

// 3. Obtener stock del día
$stmtStock = $pdo->prepare("SELECT s.*, p.nombre as producto_nombre, p.precio FROM stock_diario s JOIN productos p ON s.producto_id = p.id WHERE s.fecha = ?");
$stmtStock->execute([$hoy]);
$stockHoy = $stmtStock->fetchAll();

$stockAceptado = false;
$totalStockEnviado = 0;
foreach ($stockHoy as $st) {
    if ($st['aceptado'] == 1) {
        $stockAceptado = true;
    }
    $totalStockEnviado += (int) $st['cantidad_enviada'];
}

// 4. Obtener ventas registradas hoy con desglose Efectivo vs QR
$stmtVentas = $pdo->prepare("
    SELECT 
        SUM(cantidad) as total_unidades, 
        SUM(monto) as total_dinero,
        SUM(CASE WHEN metodo_pago = 'efectivo' THEN monto ELSE 0 END) as dinero_efectivo,
        SUM(CASE WHEN metodo_pago = 'qr' THEN monto ELSE 0 END) as dinero_qr
    FROM ventas 
    WHERE DATE(fecha_hora) = ?
");
$stmtVentas->execute([$hoy]);
$resVentas = $stmtVentas->fetch();

$unidadesVendidasHoy = (int) ($resVentas['total_unidades'] ?? 0);
$dineroRecaudadoHoy = (float) ($resVentas['total_dinero'] ?? 0.00);
$dineroEfectivoHoy = (float) ($resVentas['dinero_efectivo'] ?? 0.00);
$dineroQrHoy = (float) ($resVentas['dinero_qr'] ?? 0.00);

// 5. Verificar si el turno ya fue cerrado hoy
$stmtCierre = $pdo->prepare("SELECT * FROM cierres WHERE fecha = ?");
$stmtCierre->execute([$hoy]);
$cierreHoy = $stmtCierre->fetch();
?>
<!DOCTYPE html>
<html lang="es" class="theme-light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Carrito Sueños — POS Celular</title>
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
        rel="stylesheet">
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

        .miniletrero-vendidas {
            background: rgba(22, 163, 74, 0.12);
            color: #16a34a;
            border: 1px solid rgba(22, 163, 74, 0.25);
        }

        .miniletrero-restantes {
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
            border: 1px solid rgba(37, 99, 235, 0.25);
        }

        .miniletrero-efectivo {
            background: rgba(217, 119, 6, 0.12);
            color: #d97706;
            border: 1px solid rgba(217, 119, 6, 0.25);
        }

        .miniletrero-qr {
            background: rgba(147, 51, 234, 0.12);
            color: #9333ea;
            border: 1px solid rgba(147, 51, 234, 0.25);
        }

        .btn-touch-product {
            background: var(--btn-product-bg);
            border: 2px solid var(--btn-product-border);
            border-radius: 18px;
            padding: 18px 14px;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
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

        /* SECCIÓN CARRITO DEL PEDIDO ACTUAL */
        .order-cart-card {
            background: var(--card-bg);
            border: 2px solid var(--card-border);
            border-radius: 20px;
            padding: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
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

        .btn-checkout-efectivo {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.35);
            transition: all 0.15s ease;
        }

        .btn-checkout-efectivo:active {
            transform: scale(0.96);
        }

        .btn-checkout-qr {
            background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%);
            color: #ffffff;
            border: none;
            border-radius: 16px;
            padding: 16px;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 15px rgba(147, 51, 234, 0.35);
            transition: all 0.15s ease;
        }

        .btn-checkout-qr:active {
            transform: scale(0.96);
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
    </style>
</head>

<body>

    <!-- CABECERA DE MONITOR CON MINILETREROS Y TOGGLE TEMA -->
    <div class="header-counter py-2 px-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-extrabold fs-6 text-gradient d-flex align-items-center" style="color: var(--text-main);">
                <i class="bi bi-shop me-2 text-warning fs-5"></i>Carrito Sueños
            </span>
            <!-- Botón Cambiar Modo Claro / Oscuro -->
            <button type="button" onclick="toggleTheme()"
                class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">
                <span id="theme-icon"><i class="bi bi-moon-stars-fill me-1"></i> Modo Oscuro</span>
            </button>
        </div>

        <!-- LETREROS DE CONTADORES DESTACADOS -->
        <div class="row g-2 text-center">
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-vendidas">VENDIDAS</span>
                    <div class="fs-5 fw-extrabold text-success" id="counter-vendidas"><?= $unidadesVendidasHoy ?> u.
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-restantes">RESTANTES</span>
                    <?php $stockRestante = max(0, $totalStockEnviado - $unidadesVendidasHoy); ?>
                    <div class="fs-5 fw-extrabold text-primary" id="counter-stock">
                        <?= $stockAceptado ? $stockRestante : 0 ?> u.</div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-efectivo">EFECTIVO</span>
                    <div class="fs-6 fw-extrabold text-warning" id="counter-efectivo">Bs.
                        <?= number_format($dineroEfectivoHoy, 0) ?></div>
                </div>
            </div>
            <div class="col-3">
                <div class="p-1 rounded-3" style="background: var(--card-bg); border: 1px solid var(--card-border);">
                    <span class="miniletrero-badge miniletrero-qr">PAGO QR</span>
                    <div class="fs-6 fw-extrabold" style="color: #9333ea;" id="counter-qr">Bs.
                        <?= number_format($dineroQrHoy, 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid pt-3 px-3">

        <?php if ($cierreHoy): ?>
            <!-- TURNO CERRADO -->
            <div class="text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h4 class="fw-bold">¡Turno Cerrado!</h4>
                <p class="text-muted small">Has finalizado la jornada de hoy (<?= date('d/m/Y') ?>).</p>
                <div class="card border-0 rounded-4 p-3 text-start mx-auto mt-4 shadow-sm"
                    style="max-width: 400px; background-color: var(--card-bg); border: 1px solid var(--card-border);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Salteñas Vendidas:</span>
                        <span class="fw-bold text-success"><?= $cierreHoy['total_vendidas'] ?> u.</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Stock Sobrante:</span>
                        <span class="fw-bold text-primary"><?= $cierreHoy['total_sobrantes'] ?> u.</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cobrado en Efectivo:</span>
                        <span class="fw-bold text-warning">Bs.
                            <?= number_format($cierreHoy['dinero_efectivo'] ?? $cierreHoy['dinero_cobrado'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cobrado en QR:</span>
                        <span class="fw-bold" style="color: #9333ea;">Bs.
                            <?= number_format($cierreHoy['dinero_qr'] ?? 0, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="fw-bold">Total General:</span>
                        <span class="fw-bold text-dark fs-5">Bs.
                            <?= number_format($cierreHoy['dinero_cobrado'], 2) ?></span>
                    </div>
                    <?php if (!empty($cierreHoy['observaciones'])): ?>
                        <div class="mt-2 pt-2 border-top">
                            <span class="text-muted extra-small d-block fw-bold">Observaciones / Salteñas dañadas:</span>
                            <span class="small"><?= htmlspecialchars($cierreHoy['observaciones']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (!$stockAceptado): ?>
            <!-- PANTALLA 1: RECEPCIÓN Y ACEPTACIÓN DE STOCK -->
            <div class="text-center py-3">
                <div class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">
                    <i class="bi bi-box-seam me-1"></i> Stock Despachado Hoy
                </div>
                <h4 class="fw-bold mb-1">Confirmar Recepción</h4>
                <p class="text-muted small mb-4">Verifica la cantidad de salteñas entregadas por Central:</p>

                <div class="card border-0 rounded-4 p-3 mb-4 text-start shadow-sm"
                    style="background-color: var(--card-bg); border: 1px solid var(--card-border);">
                    <?php foreach ($stockHoy as $st): ?>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="fw-bold fs-6"
                                style="color: var(--text-main);"><?= htmlspecialchars($st['producto_nombre']) ?></span>
                            <span
                                class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fs-6"><?= $st['cantidad_enviada'] ?>
                                unidades</span>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <span class="fw-bold text-uppercase text-muted small">Total Despachado:</span>
                        <span class="fs-5 fw-extrabold text-warning"><?= $totalStockEnviado ?> u.</span>
                    </div>
                </div>

                <button type="button" onclick="aceptarStock()"
                    class="btn btn-warning btn-lg w-100 py-3 rounded-4 fw-bold shadow-lg text-dark">
                    <i class="bi bi-check-lg me-2"></i> ACEPTAR STOCK E INICIAR VENTA
                </button>
            </div>

        <?php else: ?>
            <!-- PANTALLA 2: VENTA RÁPIDA CON CARRITO MULTI-PRODUCTO -->

            <!-- CARRITO DEL PEDIDO ACTUAL -->
            <div class="order-cart-card">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="fw-bold text-uppercase small text-muted">
                        <i class="bi bi-cart3 me-1 text-primary"></i> Pedido Actual
                    </span>
                    <button type="button" onclick="vaciarCarrito()"
                        class="btn btn-link text-danger text-decoration-none p-0 extra-small fw-bold" id="btn-vaciar-cart"
                        style="display: none;">
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

                <!-- RESUMEN DEL PEDIDO -->
                <div class="pt-3 border-top mt-2 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted extra-small d-block fw-bold">TOTAL PEDIDO</span>
                        <span class="fs-4 fw-extrabold text-success" id="cart-total-monto">Bs. 0.00</span>
                    </div>
                    <div class="text-end">
                        <span class="text-muted extra-small d-block fw-bold">SALTEÑAS</span>
                        <span class="fs-5 fw-extrabold text-primary" id="cart-total-unidades">0 u.</span>
                    </div>
                </div>

                <!-- BOTONES DE COBRO FINAL (EFECTIVO Y QR) -->
                <div class="row g-2 mt-3" id="checkout-buttons-container" style="display: none;">
                    <div class="col-6">
                        <button type="button" onclick="procesarCobroOrden('efectivo')" class="btn-checkout-efectivo w-100">
                            <i class="bi bi-cash-stack d-block fs-4 mb-1"></i>
                            Cobrar EFECTIVO
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" onclick="procesarCobroOrden('qr')" class="btn-checkout-qr w-100">
                            <i class="bi bi-qr-code-scan d-block fs-4 mb-1"></i>
                            Cobrar por QR
                        </button>
                    </div>
                </div>
            </div>

            <!-- TARJETAS TIPOGRÁFICAS PARA SELECCIONAR SALTEÑAS (SUMAN AL CARRITO) -->
            <div class="mb-2 d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-grid-fill me-1"></i> Salteñas por
                    Unidad</span>
            </div>

            <div class="row g-3 mb-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="col-6">
                        <button type="button"
                            onclick="agregarAlCarrito('individual', <?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio'] ?>, 1)"
                            class="btn-touch-product">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-extrabold fs-5"
                                    style="color: var(--text-main);"><?= htmlspecialchars($prod['nombre']) ?></span>
                                <span class="badge bg-warning text-dark rounded-pill fw-extrabold px-2 py-1">Bs.
                                    <?= number_format($prod['precio'], 0) ?></span>
                            </div>
                            <div class="text-muted extra-small fw-semibold">+ Añadir al Pedido</div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PROMOCIONES / COMBOS DE CENTRAL -->
            <?php if (count($promociones) > 0): ?>
                <div class="mb-2">
                    <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-stars me-1 text-warning"></i>
                        Promociones Configuradas</span>
                </div>
                <div class="row g-3 mb-4">
                    <?php foreach ($promociones as $promo): ?>
                        <div class="col-12">
                            <button type="button"
                                onclick="agregarAlCarrito('promocion', <?= $promo['id'] ?>, '<?= addslashes($promo['nombre']) ?>', <?= $promo['precio'] ?>, <?= $promo['unidades'] ?>)"
                                class="btn-touch-promo">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-extrabold fs-6 d-block text-warning mb-1"><i
                                                class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($promo['nombre']) ?></span>
                                        <span class="text-white opacity-90 small">Descuenta <?= $promo['unidades'] ?> salteñas del
                                            stock</span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-extrabold text-white">Bs.
                                            <?= number_format($promo['precio'], 0) ?></span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>

    <?php if ($stockAceptado && !$cierreHoy): ?>
        <!-- BOTÓN FIJO INFERIOR: CERRAR TURNO -->
        <div class="floating-footer">
            <button type="button" class="btn btn-outline-danger w-100 py-3 rounded-4 fw-bold" data-bs-toggle="modal"
                data-bs-target="#modalCierre">
                <i class="bi bi-door-closed-fill me-2"></i> CERRAR TURNO DEL DÍA
            </button>
        </div>
    <?php endif; ?>

    <!-- MODAL DE CIERRE DE TURNO -->
    <div class="modal fade" id="modalCierre" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4"
                style="background-color: var(--card-bg); border: 1px solid var(--card-border);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" style="color: var(--text-main);"><i
                            class="bi bi-clipboard-check text-warning me-2"></i>Cierre de Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Revisa el resumen final de tu jornada antes de enviar:</p>

                    <div class="p-3 rounded-3 mb-3"
                        style="background: rgba(0,0,0,0.04); border: 1px solid var(--card-border);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Salteñas Vendidas:</span>
                            <span class="fw-bold text-success" id="modal-total-vendidas"><?= $unidadesVendidasHoy ?>
                                u.</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Stock Sobrante Estimado:</span>
                            <span class="fw-bold text-primary" id="modal-total-sobrantes"><?= $stockRestante ?>
                                u.</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Recaudado en Efectivo:</span>
                            <span class="fw-bold text-warning" id="modal-dinero-efectivo">Bs.
                                <?= number_format($dineroEfectivoHoy, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Recaudado en QR:</span>
                            <span class="fw-bold" style="color: #9333ea;" id="modal-dinero-qr">Bs.
                                <?= number_format($dineroQrHoy, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-bold text-dark">Total Dinero General:</span>
                            <span class="fw-bold text-dark fs-5" id="modal-total-dinero">Bs.
                                <?= number_format($dineroRecaudadoHoy, 2) ?></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Salteñas Dañadas / Rotas u Observaciones:</label>
                        <textarea id="observaciones-cierre" rows="3" class="form-control rounded-3"
                            placeholder="Ej: Se rompió 1 salteña de pollo al transportar..."></textarea>
                    </div>

                    <button type="button" onclick="confirmarCierreTurno()"
                        class="btn btn-warning w-100 py-3 rounded-3 fw-bold text-dark">
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

        // ESTADO DEL CARRITO TEMPORAL DEL PEDIDO ACTUAL
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
            const emptyMsg = document.getElementById('cart-empty-msg');
            const btnVaciar = document.getElementById('btn-vaciar-cart');
            const checkoutBtns = document.getElementById('checkout-buttons-container');

            if (carrito.length === 0) {
                container.innerHTML = '<div class="text-center py-3 text-muted small"><i class="bi bi-hand-index-thumb fs-4 d-block mb-1"></i>Toca las salteñas de abajo para agregarlas al pedido</div>';
                btnVaciar.style.display = 'none';
                checkoutBtns.style.display = 'none';
                document.getElementById('cart-total-monto').innerText = 'Bs. 0.00';
                document.getElementById('cart-total-unidades').innerText = '0 u.';
                return;
            }

            btnVaciar.style.display = 'inline-block';
            checkoutBtns.style.display = 'flex';

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
                            <span class="fw-bold text-success ms-2" style="min-width: 60px; text-align: right;">Bs. ${subtotal.toFixed(0)}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            document.getElementById('cart-total-monto').innerText = 'Bs. ' + totalMonto.toFixed(2);
            document.getElementById('cart-total-unidades').innerText = totalUnidades + ' u.';
        }

        function procesarCobroOrden(metodo) {
            if (carrito.length === 0) return;
            if (navigator.vibrate) navigator.vibrate(60);

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

            // Actualizar interfaz al instante
            unidadesVendidas += totalSalteñasVendidas;
            if (metodo === 'efectivo') {
                dineroEfectivo += totalCobrado;
            } else {
                dineroQr += totalCobrado;
            }
            actualizarIndicadores();

            // Limpiar carrito
            vaciarCarrito();

            // Enviar orden al servidor
            fetch('api.php?accion=registrar_venta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: payloadItems,
                    metodo_pago: metodo
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
    </script>
</body>

</html>