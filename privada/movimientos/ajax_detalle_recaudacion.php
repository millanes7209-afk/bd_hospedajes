<?php
session_start();
require_once("../../conexion.php");

if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    echo "<div class='alert alert-danger'>Acceso Denegado.</div>";
    exit();
}

$recaudacionID = $_POST['recaudacionID'] ?? null;
$empresaID = $_SESSION['empresaID'];

if (!$recaudacionID) {
    echo "<div class='alert alert-danger'>Error: No se proporcionó el identificador de la recaudación.</div>";
    exit();
}

// 1. Obtener datos de la recaudación
$sqlRec = "SELECT r.*, u1.usuario as recepcionista, u2.usuario as propietario
           FROM recaudaciones r
           INNER JOIN usuarios u1 ON r.usuariorecepcionistaID = u1.usuarioID
           INNER JOIN usuarios u2 ON r.usuariopropietarioID = u2.usuarioID
           WHERE r.recaudacionID = ? AND r.empresaID = ?";
$recInfo = $db->obtenerFila($sqlRec, [$recaudacionID, $empresaID]);

if (!$recInfo) {
    echo "<div class='alert alert-warning'>No se encontró la recaudación.</div>";
    exit();
}

// 2. Obtener Movimientos de esta recaudación
$sqlMovs = "SELECT 
                m.movimientoID, m.tipo, m.concepto, m.detalle, m.monto, m._fec_insercion,
                fp.tipo AS forma_pago, m.cajaID
            FROM movimientos m
            INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
            WHERE m.recaudacionID = ? AND m.empresaID = ? AND m._estado <> 'X'
            ORDER BY m.movimientoID ASC";
$movimientos = $db->obtenerTodo($sqlMovs, [$recaudacionID, $empresaID]);

// 3. Renderizar Vista
$totalIngresos = 0;
$totalEgresos = 0;
$cajaID_ref = $movimientos ? $movimientos[0]['cajaID'] : 'N/A';
?>
<div class="row mb-3">
    <div class="col-md-6">
        <p class="mb-1"><strong>Recepcionista (Entregó):</strong> <?= htmlspecialchars($recInfo['recepcionista']) ?></p>
        <p class="mb-0"><strong>Propietario (Recibió):</strong> <?= htmlspecialchars($recInfo['propietario']) ?></p>
    </div>
    <div class="col-md-6 text-end">
        <p class="mb-1"><strong>Nro Comprobante:</strong> <span class="fw-bold"><?= $recInfo['comprobante_nro'] ?></span></p>
        <p class="mb-0"><strong>Turno Origen:</strong> #<?= $cajaID_ref ?></p>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped" style="font-size: 0.9rem;">
        <thead class="table-dark">
            <tr>
                <th>Fecha/Hora</th>
                <th>Tipo</th>
                <th>Concepto y Detalle</th>
                <th>F. Pago</th>
                <th class="text-end">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="5" class="text-center py-3 text-muted">No hay movimientos registrados en este comprobante.</td></tr>
            <?php else: ?>
                <?php foreach ($movimientos as $mov): 
                    if ($mov['tipo'] === 'INGRESO') $totalIngresos += $mov['monto'];
                    if ($mov['tipo'] === 'EGRESO') $totalEgresos += $mov['monto'];
                ?>
                <tr>
                    <td class="align-middle text-muted"><?= date('d/m H:i', strtotime($mov['_fec_insercion'])) ?></td>
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
            <tr class="table-success">
                <td colspan="4" class="text-end fs-5">TOTAL RECAUDADO LÍQUIDO:</td>
                <td class="text-end fs-5 text-dark">Bs. <?= number_format($recInfo['monto'], 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>
