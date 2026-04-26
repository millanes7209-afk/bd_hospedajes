<?php
session_start();
require_once("../../conexion.php");

if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    echo "<div class='alert alert-danger'>Acceso Denegado.</div>";
    exit();
}

$cajaID = $_POST['cajaID'] ?? null;
$empresaID = $_SESSION['empresaID'];

if (!$cajaID) {
    echo "<div class='alert alert-danger'>Error: No se proporcionó el identificador del turno.</div>";
    exit();
}

// 1. Obtener datos de la caja
$sqlCaja = "SELECT c.cajaID, u.usuario as recepcionista, c.fecha_apertura, c.fecha_cierre
            FROM cajas c
            INNER JOIN usuarios u ON c.usuarioID = u.usuarioID
            WHERE c.cajaID = ? AND c.empresaID = ? AND c._estado <> 'X'";
$cajaInfo = $db->obtenerFila($sqlCaja, [$cajaID, $empresaID]);

if (!$cajaInfo) {
    echo "<div class='alert alert-warning'>No se encontró información del turno seleccionado.</div>";
    exit();
}

// 2. Obtener Movimientos
$sqlMovs = "SELECT 
                m.movimientoID, m.tipo, m.concepto, m.detalle, m.monto, m._fec_insercion,
                fp.tipo AS forma_pago
            FROM movimientos m
            INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
            WHERE m.cajaID = ? AND m.empresaID = ? AND m._estado <> 'X'
            ORDER BY m.movimientoID ASC";
$movimientos = $db->obtenerTodo($sqlMovs, [$cajaID, $empresaID]);

// 3. Renderizar Vista (HTML)
$totalIngresos = 0;
$totalEgresos = 0;
?>
<!-- Encabezado del Turno Estilo Formulario -->
<div class="card mb-3 shadow-none border">
    <div class="card-header bg-light py-2">
        <h6 class="mb-0 fw-bold">DATOS DEL TURNO #<?= $cajaInfo['cajaID'] ?></h6>
    </div>
    <div class="card-body py-2">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-dark"><strong>Recepcionista:</strong> <?= htmlspecialchars($cajaInfo['recepcionista']) ?></p>
                <p class="mb-0 text-dark"><strong>Apertura:</strong> <?= date('d/m/Y H:i', strtotime($cajaInfo['fecha_apertura'])) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1 text-dark"><strong>Cierre:</strong> <?= $cajaInfo['fecha_cierre'] ? date('d/m/Y H:i', strtotime($cajaInfo['fecha_cierre'])) : '<span class="text-danger fw-bold">TURNO ABIERTO</span>' ?></p>
                <p class="mb-0 text-dark"><strong>Empresa ID:</strong> <?= $empresaID ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Movimientos Limpia -->
<div class="table-responsive mb-3">
    <table class="table table-hover border" style="font-size: 0.85rem;">
        <thead class="bg-light text-dark">
            <tr>
                <th class="border-bottom">Hora</th>
                <th class="border-bottom">Tipo</th>
                <th class="border-bottom">Concepto y Detalle</th>
                <th class="border-bottom text-center">F. Pago</th>
                <th class="border-bottom text-end">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No hay movimientos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($movimientos as $mov): 
                    if ($mov['tipo'] === 'INGRESO') $totalIngresos += $mov['monto'];
                    if ($mov['tipo'] === 'EGRESO') $totalEgresos += $mov['monto'];
                ?>
                <tr>
                    <td class="text-muted"><?= date('H:i', strtotime($mov['_fec_insercion'])) ?></td>
                    <td>
                        <small class="fw-bold <?= $mov['tipo'] === 'INGRESO' ? 'text-success' : 'text-danger' ?>">
                            <?= $mov['tipo'] ?>
                        </small>
                    </td>
                    <td>
                        <span class="text-dark"><strong><?= htmlspecialchars($mov['concepto']) ?></strong></span>
                        <?php if(!empty($mov['detalle'])): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($mov['detalle']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="text-center text-dark"><?= htmlspecialchars($mov['forma_pago']) ?></td>
                    <td class="text-end fw-bold text-dark">
                        <?= number_format($mov['monto'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Resumen de Totales Estilo Card -->
<div class="row justify-content-end">
    <div class="col-md-6">
        <div class="card border-0 bg-light">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-dark">Total Ingresos:</span>
                    <span class="fw-bold text-success">Bs. <?= number_format($totalIngresos, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-dark">Total Egresos:</span>
                    <span class="fw-bold text-danger">Bs. <?= number_format($totalEgresos, 2) ?></span>
                </div>
                <div class="d-flex justify-content-between border-top pt-2">
                    <span class="h5 mb-0 text-dark fw-bold">SALDO LÍQUIDO:</span>
                    <span class="h5 mb-0 text-dark fw-bold">Bs. <?= number_format($totalIngresos - $totalEgresos, 2) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
