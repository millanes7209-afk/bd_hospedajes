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

// 2. Obtener Movimientos de esta recaudación desde la vista unificada
$sqlMovs = "SELECT 
                movimientoID, tipo, concepto, monto, fecha as _fec_insercion,
                forma_pago, cajaID
            FROM " . $db->getVistaMovimientos() . " as t
            WHERE recaudacionID = ? AND empresaID = ?
            ORDER BY movimientoID ASC";
$movimientos = $db->obtenerTodo($sqlMovs, [$recaudacionID, $empresaID]);

// 3. Renderizar Vista
$totalIngresos = 0;
$totalEgresos = 0;
$cajaID_ref = $movimientos ? $movimientos[0]['cajaID'] : 'N/A';
?>
<!-- Datos de la Recaudación Estilo Card -->
<div class="card mb-3 shadow-none border">
    <div class="card-header bg-light py-2">
        <h6 class="mb-0 fw-bold">COMPROBANTE DE RECAUDACIÓN #<?= $recInfo['comprobante_nro'] ?></h6>
    </div>
    <div class="card-body py-2">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1 text-dark"><strong>Recepcionista (Entregó):</strong> <?= htmlspecialchars($recInfo['recepcionista']) ?></p>
                <p class="mb-0 text-dark"><strong>Propietario (Recibió):</strong> <?= htmlspecialchars($recInfo['propietario']) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1 text-dark"><strong>Fecha Recaudación:</strong> <?= date('d/m/Y H:i', strtotime($recInfo['fecha'])) ?></p>
                <p class="mb-0 text-dark"><strong>Referencia:</strong> Turno #<?= $cajaID_ref ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Movimientos Limpia -->
<div class="table-responsive mb-3">
    <table class="table table-hover border" style="font-size: 0.85rem;">
        <thead class="bg-light text-dark">
            <tr>
                <th class="border-bottom">Fecha/Hora</th>
                <th class="border-bottom">Tipo</th>
                <th class="border-bottom">Concepto</th>
                <th class="border-bottom text-center">F. Pago</th>
                <th class="border-bottom text-end">Monto (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No hay movimientos vinculados.</td></tr>
            <?php else: ?>
                <?php foreach ($movimientos as $mov): 
                    if ($mov['tipo'] === 'INGRESO') $totalIngresos += $mov['monto'];
                    if ($mov['tipo'] === 'EGRESO') $totalEgresos += $mov['monto'];
                ?>
                <tr>
                    <td class="text-muted small"><?= date('d/m H:i', strtotime($mov['_fec_insercion'])) ?></td>
                    <td>
                        <small class="fw-bold <?= $mov['tipo'] === 'INGRESO' ? 'text-success' : 'text-danger' ?>">
                            <?= $mov['tipo'] ?>
                        </small>
                    </td>
                    <td>
                        <span class="text-dark"><strong><?= htmlspecialchars($mov['concepto']) ?></strong></span>
                    </td>
                    <td class="text-center text-dark small"><?= htmlspecialchars($mov['forma_pago']) ?></td>
                    <td class="text-end fw-bold text-dark">
                        <?= number_format($mov['monto'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Total Recaudado -->
<div class="row justify-content-end">
    <div class="col-md-5">
        <div class="card border-0 bg-light">
            <div class="card-body p-3 text-center">
                <span class="text-muted small d-block mb-1">TOTAL RECAUDADO LÍQUIDO:</span>
                <span class="h4 mb-0 text-dark fw-bold">Bs. <?= number_format($recInfo['monto'], 2) ?></span>
            </div>
        </div>
    </div>
</div>
