<?php
require_once __DIR__ . '/../config/database.php';

$pdo = getDBConnection();
$hoy = date('Y-m-d');

// 1. Obtener productos
$productos = [];
try {
    $productos = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY nombre")->fetchAll();
} catch (Exception $e) {
    // Si aún no se corrió install.php
    header('Location: install.php');
    exit;
}

// 2. Obtener promociones
$promociones = $pdo->query("SELECT * FROM promociones WHERE activo = 1 ORDER BY nombre")->fetchAll();

// 3. Obtener stock del día
$stmtStock = $pdo->prepare("SELECT s.*, p.nombre as producto_nombre, p.precio FROM stock_diario s JOIN productos p ON s.producto_id = p.id WHERE s.fecha = ?");
$stmtStock->execute([$hoy]);
$stockHoy = $stmtStock->fetchAll();

// Verificar si se ha aceptado el stock
$stockAceptado = false;
$totalStockEnviado = 0;
foreach ($stockHoy as $st) {
    if ($st['aceptado'] == 1) {
        $stockAceptado = true;
    }
    $totalStockEnviado += (int) $st['cantidad_enviada'];
}

// 4. Obtener ventas registradas hoy
$stmtVentas = $pdo->prepare("SELECT SUM(cantidad) as total_unidades, SUM(monto) as total_dinero FROM ventas WHERE DATE(fecha_hora) = ?");
$stmtVentas->execute([$hoy]);
$resVentas = $stmtVentas->fetch();
$unidadesVendidasHoy = (int) ($resVentas['total_unidades'] ?? 0);
$dineroRecaudadoHoy = (float) ($resVentas['total_dinero'] ?? 0.00);

// Verificar si el turno ya fue cerrado hoy
$stmtCierre = $pdo->prepare("SELECT * FROM cierres WHERE fecha = ?");
$stmtCierre->execute([$hoy]);
$cierreHoy = $stmtCierre->fetch();
?>
<!DOCTYPE html>
<html lang="es">

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
        :root {
            --bg-main: #0f172a;
            --card-bg: #1e293b;
            --accent-yellow: #fbbf24;
            --accent-green: #22c55e;
            --accent-red: #ef4444;
            --text-main: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding-bottom: 95px;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .header-counter {
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .btn-touch-product {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 18px 14px;
            transition: all 0.15s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .btn-touch-product:active {
            transform: scale(0.95);
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }

        .btn-touch-promo {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 16px;
        }

        .btn-touch-promo:active {
            transform: scale(0.95);
        }

        .badge-stock {
            background: rgba(251, 191, 36, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .floating-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 16px;
            z-index: 999;
        }
    </style>
</head>

<body>

    <!-- MONITOR DE CAJA Y STOCK EN TIEMPO REAL -->
    <div class="header-counter py-3 px-3">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <span class="text-uppercase text-muted extra-small fw-bold tracking-wider d-block"
                    style="font-size: 0.7rem;">RECAUDADO</span>
                <span class="fs-4 fw-extrabold text-warning mb-0" id="counter-dinero">Bs.
                    <?= number_format($dineroRecaudadoHoy, 2) ?>
                </span>
            </div>
            <div class="text-center">
                <span class="text-uppercase text-muted extra-small fw-bold d-block"
                    style="font-size: 0.7rem;">VENDIDAS</span>
                <span class="fs-4 fw-extrabold text-success mb-0" id="counter-vendidas">
                    <?= $unidadesVendidasHoy ?> u.
                </span>
            </div>
            <div class="text-end">
                <span class="text-uppercase text-muted extra-small fw-bold d-block"
                    style="font-size: 0.7rem;">STOCK</span>
                <?php $stockRestante = max(0, $totalStockEnviado - $unidadesVendidasHoy); ?>
                <span class="fs-4 fw-extrabold text-info mb-0" id="counter-stock">
                    <?= $stockAceptado ? $stockRestante : 0 ?> u.
                </span>
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
                <p class="text-muted small">Has finalizado la jornada de hoy (
                    <?= date('d/m/Y') ?>).
                </p>
                <div class="card border-0 rounded-4 p-3 text-start mx-auto mt-4"
                    style="max-width: 400px; background-color: var(--card-bg);">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Vendidas:</span>
                        <span class="fw-bold text-success">
                            <?= $cierreHoy['total_vendidas'] ?> salteñas
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Sobrantes:</span>
                        <span class="fw-bold text-warning">
                            <?= $cierreHoy['total_sobrantes'] ?> salteñas
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dinero Cobrado:</span>
                        <span class="fw-bold text-warning">Bs.
                            <?= number_format($cierreHoy['dinero_cobrado'], 2) ?>
                        </span>
                    </div>
                    <?php if (!empty($cierreHoy['observaciones'])): ?>
                        <div class="mt-2 pt-2 border-top border-secondary">
                            <span class="text-muted extra-small d-block">Observaciones / Salteñas dañadas:</span>
                            <span class="small text-light">
                                <?= htmlspecialchars($cierreHoy['observaciones']) ?>
                            </span>
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

                <div class="card border-0 rounded-4 p-3 mb-4 text-start" style="background-color: var(--card-bg);">
                    <?php foreach ($stockHoy as $st): ?>
                        <div
                            class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary border-opacity-25">
                            <span class="fw-semibold text-light fs-6"><i class="bi bi-dot text-warning fs-4"></i>
                                <?= htmlspecialchars($st['producto_nombre']) ?>
                            </span>
                            <span class="badge badge-stock px-3 py-2 rounded-pill fs-6">
                                <?= $st['cantidad_enviada'] ?> unidades
                            </span>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between align-items-center pt-3">
                        <span class="fw-bold text-uppercase text-muted small">Total Despachado:</span>
                        <span class="fs-5 fw-extrabold text-warning">
                            <?= $totalStockEnviado ?> u.
                        </span>
                    </div>
                </div>

                <button type="button" onclick="aceptarStock()"
                    class="btn btn-warning btn-lg w-100 py-3 rounded-4 fw-bold shadow-lg text-dark">
                    <i class="bi bi-check-lg me-2"></i> ACEPTAR STOCK E INICIAR VENTA
                </button>
            </div>

        <?php else: ?>
            <!-- PANTALLA 2: BOTONERA DE VENTA RÁPIDA -->
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-grid-fill me-1"></i> Toca para Vender
                    (+1)</span>
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill"><i
                        class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>En Venta</span>
            </div>

            <!-- Botonera de Productos Individuales -->
            <div class="row g-3 mb-4">
                <?php foreach ($productos as $prod): ?>
                    <div class="col-6">
                        <button type="button"
                            onclick="registrarVentaProducto(<?= $prod['id'] ?>, '<?= addslashes($prod['nombre']) ?>', <?= $prod['precio'] ?>)"
                            class="btn btn-touch-product w-100 text-start text-light">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="fs-2">🥐</span>
                                <span class="badge bg-warning text-dark rounded-pill fw-bold">Bs.
                                    <?= number_format($prod['precio'], 0) ?>
                                </span>
                            </div>
                            <div class="fw-extrabold fs-5 mb-1">
                                <?= htmlspecialchars($prod['nombre']) ?>
                            </div>
                            <div class="text-muted extra-small">Tocar para sumar 1</div>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Promociones / Combos -->
            <?php if (count($promociones) > 0): ?>
                <div class="mb-2">
                    <span class="fw-bold small text-uppercase text-muted"><i class="bi bi-stars me-1 text-warning"></i>
                        Promociones de Central</span>
                </div>
                <div class="row g-3 mb-4">
                    <?php foreach ($promociones as $promo): ?>
                        <div class="col-12">
                            <button type="button"
                                onclick="registrarVentaPromo(<?= $promo['id'] ?>, '<?= addslashes($promo['nombre']) ?>', <?= $promo['unidades'] ?>, <?= $promo['precio'] ?>)"
                                class="btn btn-touch-promo w-100 text-start text-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-extrabold fs-6 d-block text-warning"><i class="bi bi-tag-fill me-1"></i>
                                            <?= htmlspecialchars($promo['nombre']) ?>
                                        </span>
                                        <span class="text-light opacity-75 small">Descuenta
                                            <?= $promo['unidades'] ?> salteñas del stock
                                        </span>
                                    </div>
                                    <div class="text-end">
                                        <span class="fs-4 fw-extrabold text-light">Bs.
                                            <?= number_format($promo['precio'], 0) ?>
                                        </span>
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
        <!-- BOTÓN DE FIJO INFERIOR: CERRAR TURNO -->
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
            <div class="modal-content border-0 rounded-4 text-light" style="background-color: var(--card-bg);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-check text-warning me-2"></i>Cierre de
                        Turno</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Revisa el resumen final de tu jornada antes de enviar:</p>

                    <div class="bg-dark bg-opacity-50 p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Salteñas Vendidas:</span>
                            <span class="fw-bold text-success" id="modal-total-vendidas">
                                <?= $unidadesVendidasHoy ?> u.
                            </span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Stock Sobrante Estimado:</span>
                            <span class="fw-bold text-warning" id="modal-total-sobrantes">
                                <?= $stockRestante ?> u.
                            </span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Efectivo Recaudado:</span>
                            <span class="fw-bold text-warning fs-5" id="modal-total-dinero">Bs.
                                <?= number_format($dineroRecaudadoHoy, 2) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted">Aclaraciones / Salteñas Dañadas o Rotas:</label>
                        <textarea id="observaciones-cierre" rows="3"
                            class="form-control bg-dark border-secondary text-light rounded-3"
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
        let dineroRecaudado = <?= $dineroRecaudadoHoy ?>;
        let totalStockEnviado = <?= $totalStockEnviado ?>;

        function actualizarIndicadores() {
            document.getElementById('counter-vendidas').innerText = unidadesVendidas + ' u.';
            document.getElementById('counter-dinero').innerText = 'Bs. ' + dineroRecaudado.toFixed(2);
            let sobrante = Math.max(0, totalStockEnviado - unidadesVendidas);
            document.getElementById('counter-stock').innerText = sobrante + ' u.';

            // Actualizar modal
            document.getElementById('modal-total-vendidas').innerText = unidadesVendidas + ' u.';
            document.getElementById('modal-total-sobrantes').innerText = sobrante + ' u.';
            document.getElementById('modal-total-dinero').innerText = 'Bs. ' + dineroRecaudado.toFixed(2);
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

        function registrarVentaProducto(id, nombre, precio) {
            if (navigator.vibrate) navigator.vibrate(40);

            unidadesVendidas += 1;
            dineroRecaudado += parseFloat(precio);
            actualizarIndicadores();

            fetch('api.php?accion=registrar_venta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tipo: 'individual',
                    producto_id: id,
                    cantidad: 1,
                    monto: precio
                })
            });
        }

        function registrarVentaPromo(id, nombre, unidades, precio) {
            if (navigator.vibrate) navigator.vibrate(60);

            unidadesVendidas += parseInt(unidades);
            dineroRecaudado += parseFloat(precio);
            actualizarIndicadores();

            fetch('api.php?accion=registrar_venta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    tipo: 'promocion',
                    promocion_id: id,
                    cantidad: unidades,
                    monto: precio
                })
            });
        }

        function confirmarCierreTurno() {
            let obs = document.getElementById('observaciones-cierre').value;
            let sobrantes = Math.max(0, totalStockEnviado - unidadesVendidas);

            fetch('api.php?accion=cerrar_turno', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    total_vendidas: unidadesVendidas,
                    total_sobrantes: sobrantes,
                    dinero_cobrado: dineroRecaudado,
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