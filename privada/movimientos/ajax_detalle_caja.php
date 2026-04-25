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
            WHERE c.cajaID = ? AND c.empresaID = ?";
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
<div class="row mb-3">
    <div class="col-md-6">
        <p class="mb-1"><strong>Recepcionista:</strong> <?= htmlspecialchars($cajaInfo['recepcionista']) ?></p>
        <p class="mb-0"><strong>Apertura:</strong> <?= date('d/m/Y H:i', strtotime($cajaInfo['fecha_apertura'])) ?></p>
    </div>
    <div class="col-md-6 text-end">
        <p class="mb-1"><strong>Cierre:</strong> <?= $cajaInfo['fecha_cierre'] ? date('d/m/Y H:i', strtotime($cajaInfo['fecha_cierre'])) : '<span class="text-warning fw-bold">EN CURSO</span>' ?></p>
        <p class="mb-0"><strong>Turno #</strong> <?= $cajaInfo['cajaID'] ?></p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped" style="font-size: 0.9rem;">
        <thead class="table-dark">
            <tr>
                <th>Hora</th>
                <th>Tipo</th>
                <th>Concepto y Detalle</th>
                <th>F. Pago</th>
                <th class="text-end">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="5" class="text-center py-3 text-muted">No hay movimientos registrados en este turno.</td></tr>
            <?php else: ?>
                <?php foreach ($movimientos as $mov): 
                    if ($mov['tipo'] === 'INGRESO') $totalIngresos += $mov['monto'];
                    if ($mov['tipo'] === 'EGRESO') $totalEgresos += $mov['monto'];
                ?>
                <tr>
                    <td class="align-middle text-muted"><?= date('H:i', strtotime($mov['_fec_insercion'])) ?></td>
                    <td class="align-middle">
                        <?php if($mov['tipo'] === 'INGRESO'): ?>
                            <span class="badge bg-success">INGRESO</span>
                        <?php else: ?>
                            <span class="badge bg-danger">EGRESO</span>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle">
                        <strong><?= htmlspecialchars($mov['concepto']) ?></strong>
                        <?php if(!empty($mov['detalle'])): ?>
                            <br><small class="text-muted fst-italic"><i class="fas fa-comment-dots"></i> <?= htmlspecialchars($mov['detalle']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="align-middle text-center"><small><?= htmlspecialchars($mov['forma_pago']) ?></small></td>
                    <td class="align-middle text-end fw-bold <?= $mov['tipo'] === 'INGRESO' ? 'text-success' : 'text-danger' ?>">
                        <?= $mov['tipo'] === 'INGRESO' ? '+' : '-' ?><?= number_format($mov['monto'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="4" class="text-end">TOTAL INGRESOS:</td>
                <td class="text-end text-success">+<?= number_format($totalIngresos, 2) ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-end">TOTAL EGRESOS:</td>
                <td class="text-end text-danger">-<?= number_format($totalEgresos, 2) ?></td>
            </tr>
            <tr class="table-warning">
                <td colspan="4" class="text-end fs-5">SALDO LÍQUIDO DEL TURNO:</td>
                <td class="text-end fs-5 text-dark">Bs. <?= number_format($totalIngresos - $totalEgresos, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
