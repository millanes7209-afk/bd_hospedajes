<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Extraer entorno seguro de sesión
$empresaID = $_SESSION['empresaID'] ?? null;
$caja_abierta_id = $_SESSION['caja_abierta_id'] ?? null;
$usuarioActual = $_SESSION["sesion_usuario"] ?? 'Cajero';
$rol_usuario = $_SESSION['sesion_rol'] ?? 'RECEPCIONISTA';
$es_admin = in_array($rol_usuario, ['ADMINISTRADOR', 'PROPIETARIO']);

// ── LÓGICA DE SELECCIÓN DE CAJA ───────────────────────────────────────────────
if ($es_admin) {
    // Cargar TODAS las cajas abiertas de la empresa
    $cajas_abiertas = $db->obtenerTodo(
        "SELECT c.cajaID, c.fecha_apertura, u.usuario as nombre_usuario
         FROM cajas c
         JOIN usuarios u ON c.usuarioID = u.usuarioID
         WHERE c.empresaID = ? AND c.estado = 'ABIERTA' AND c._estado <> 'X'
         ORDER BY c.fecha_apertura ASC",
        [$empresaID]
    );

    if (empty($cajas_abiertas)) {
        echo "<div class='container mt-5'><div class='alert alert-warning shadow-sm'>
                <h4><i class='fas fa-info-circle'></i> Sin Cajas Abiertas</h4>
                <p>No existe ninguna caja abierta en la empresa en este momento.</p>
              </div></div></body></html>";
        exit;
    }

    // El propietario puede elegir qué caja ver vía GET
    $caja_seleccionada = (int) ($_GET['cajaID'] ?? $cajas_abiertas[0]['cajaID']);

    // Obtener nombre del recepcionista de la caja seleccionada e inicio del turno
    $cajero_seleccionado = '';
    $inicio_turno_caja = '';
    foreach ($cajas_abiertas as $c) {
        if ($c['cajaID'] == $caja_seleccionada) {
            $cajero_seleccionado = mb_strtoupper($c['nombre_usuario']);
            $inicio_turno_caja = date('d/m/Y H:i', strtotime($c['fecha_apertura']));
            break;
        }
    }

} else {
    // RECEPCIONISTA: solo su propia caja
    if (!$caja_abierta_id) {
        echo "<div class='container mt-5'><div class='alert alert-danger shadow-sm'>
                <h4 class='alert-heading'><i class='fas fa-exclamation-triangle'></i> Acceso Denegado</h4>
                <p><strong>No existe una caja abierta en su sesión actualmente.</strong><br>
                Para visualizar los ingresos y egresos de un turno, primero debe realizar el proceso de 'Abrir Caja'.</p>
              </div></div></body></html>";
        exit;
    }

    // Obtener fecha de apertura de la caja del recepcionista actual
    $caja_datos = $db->obtenerFila("SELECT fecha_apertura FROM cajas WHERE cajaID = ?", [$caja_abierta_id]);
    $inicio_turno_caja = $caja_datos ? date('d/m/Y H:i', strtotime($caja_datos['fecha_apertura'])) : 'N/A';

    $cajas_abiertas = [];
    $caja_seleccionada = $caja_abierta_id;
    $cajero_seleccionado = mb_strtoupper($usuarioActual);
}

// ── MOVIMIENTOS DE LA CAJA SELECCIONADA ───────────────────────────────────────
$sql = "SELECT
            movimientoID,
            tipo,
            concepto AS descripcion,
            forma_pago,
            monto,
            fecha AS fecha_registro
        FROM " . $db->getVistaMovimientos() . " as t
        WHERE cajaID = ? AND empresaID = ? AND t._estado <> 'X'
        ORDER BY fecha DESC";

$movimientos_caja = $db->obtenerTodo($sql, [$caja_seleccionada, $empresaID]) ?: [];

// ── TOTALES ────────────────────────────────────────────────────────────────────
$total_ingresos = 0;
$total_egresos = 0;
$desglose_pagos = [];

foreach ($movimientos_caja as $mov) {
    $fp = mb_strtoupper($mov['forma_pago']);
    if (!isset($desglose_pagos[$fp])) {
        $desglose_pagos[$fp] = ['ingresos' => 0, 'egresos' => 0];
    }
    if ($mov['tipo'] === 'INGRESO') {
        $total_ingresos += (float) $mov['monto'];
        $desglose_pagos[$fp]['ingresos'] += (float) $mov['monto'];
    } elseif ($mov['tipo'] === 'EGRESO') {
        $total_egresos += (float) $mov['monto'];
        $desglose_pagos[$fp]['egresos'] += (float) $mov['monto'];
    }
}

$saldo_final = $total_ingresos - $total_egresos;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Flujo Financiero del Turno</title>
    <style>
        .badge-ingreso {
            background-color: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .badge-egreso {
            background-color: #dc3545;
            color: white;
            padding: 5px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .td-concepto {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .monto-td {
            font-size: 1.1rem;
        }

        .text-cajero-info {
            font-size: 1.05rem;
            font-weight: 900;
        }

        th {
            font-weight: bold !important;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- CABECERA SIMPLIFICADA (Igual a hospedajes.php) -->
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0">FLUJO FINANCIERO DEL TURNO</h3>
            <span class="text-dark text-cajero-info uppercase">
                <i class="fas fa-user-circle"></i> CAJERO: <strong><?= $cajero_seleccionado ?></strong>
                &nbsp;|&nbsp;
                <i class="fas fa-clock"></i> APERTURA: <strong><?= $inicio_turno_caja ?></strong>
            </span>
        </div>

        <div class="card-body">
            <!-- SELECTOR DE CAJA INTEGRADO EN CARD-BODY (Solo Admin/Propietario) -->
            <?php if ($es_admin && count($cajas_abiertas) > 1): ?>
                <div class="form-group row align-items-end mb-4">
                    <div class="col-md-4">
                        <label class="small fw-bold"><i class="fas fa-exchange-alt"></i> CAMBIAR DE TURNO / CAJERO:</label>
                        <select class="form-control form-control-sm"
                            onchange="window.location.href='turno.php?cajaID='+this.value">
                            <?php foreach ($cajas_abiertas as $c): ?>
                                <option value="<?= $c['cajaID'] ?>" <?= ($c['cajaID'] == $caja_seleccionada) ? 'selected' : '' ?>>
                                    <?= mb_strtoupper($c['nombre_usuario']) ?>
                                    — ABIERTA: <?= date('d/m/Y H:i', strtotime($c['fecha_apertura'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABLA DE MOVIMIENTOS -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col" width="15%">Tipo</th>
                            <th scope="col" width="35%">Concepto de Operación</th>
                            <th scope="col" width="15%">Forma de Pago</th>
                            <th scope="col" width="15%">Cajero</th>
                            <th scope="col" width="10%">Fecha y Hora</th>
                            <th scope="col" width="10%" class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($movimientos_caja) > 0): ?>
                            <?php foreach ($movimientos_caja as $mov): ?>
                                <tr>
                                    <td>
                                        <?php if ($mov['tipo'] === 'INGRESO'): ?>
                                            <span class="badge-ingreso"><i class="fas fa-plus-circle"></i> INGRESO</span>
                                        <?php else: ?>
                                            <span class="badge-egreso"><i class="fas fa-minus-circle"></i> EGRESO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="td-concepto text-dark text-uppercase">
                                        <?= htmlspecialchars($mov['descripcion']) ?>
                                        <?php if (!empty($mov['detalle'])): ?>
                                            <br><small class="text-muted text-lowercase" style="font-style: italic;">
                                                <i class="fas fa-comment-dots text-secondary"></i>
                                                <?= htmlspecialchars($mov['detalle']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary font-weight-bold">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <?= mb_strtoupper($mov['forma_pago']) ?>
                                    </td>
                                    <td class="text-secondary">
                                        <i class="fas fa-user"></i> <?= mb_strtoupper($cajero_seleccionado) ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= date('d/m/Y H:i', strtotime($mov['fecha_registro'])) ?>
                                    </td>
                                    <td
                                        class="text-right font-weight-bold monto-td <?= ($mov['tipo'] === 'INGRESO') ? 'text-success' : 'text-danger' ?>">
                                        <?= ($mov['tipo'] === 'INGRESO' ? '+' : '-') ?>
                                        <?= number_format($mov['monto'], 2, '.', ',') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-folder-open mb-3" style="font-size: 3rem; opacity: 0.5;"></i><br>
                                    <h5 class="font-weight-light">El turno actual está vacío.</h5>
                                    <p class="mb-0">No existen movimientos registrados en esta caja.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-light border-top">
                        <?php foreach ($desglose_pagos as $metodo => $valores):
                            $saldo_metodo = $valores['ingresos'] - $valores['egresos'];
                            if ($saldo_metodo == 0)
                                continue;
                            ?>
                            <tr class="text-secondary">
                                <th colspan="5" class="text-right align-middle font-weight-normal">
                                    TOTAL LÍQUIDO EN <span class="text-dark fw-bold"><?= $metodo ?></span>:
                                </th>
                                <th class="text-right">
                                    <?= number_format($saldo_metodo, 2, '.', ',') ?> Bs.
                                </th>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="bg-dark text-white">
                            <th colspan="5" class="text-right align-middle font-weight-bold">
                                <span style="font-size: 1.1rem;">SALDO TOTAL NETO DEL TURNO:</span>
                            </th>
                            <th class="text-right font-weight-bold" style="font-size: 1.2rem;">
                                <?= number_format($saldo_final, 2, '.', ',') ?> Bs.
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</body>

</html>