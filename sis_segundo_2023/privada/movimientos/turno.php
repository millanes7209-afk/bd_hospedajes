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
        .card {
            margin: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #fff;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        thead {
            color: black;
            background: #b5b5b5;
        }

        .table td,
        .table th {
            vertical-align: middle !important;
            color: black !important;
        }

        /* Indicador de Tipo Simple y Minimalista */
        .color-ingreso {
            color: #28a745 !important;
            font-weight: bold;
        }

        .color-egreso {
            color: #dc3545 !important;
            font-weight: bold;
        }

        .text-cajero-info {
            font-size: 0.95rem;
            font-weight: bold;
            color: #495057 !important;
        }

        /* Totales del pie legibles (Gris claro) */
        .total-fila-metodo {
            background-color: #f8f9fa !important;
            font-weight: normal;
        }

        .total-fila-neta {
            background-color: #e9ecef !important;
            font-weight: bold;
        }

        .total-fila-neta th,
        .total-fila-neta td {
            font-size: 1.15rem;
            color: #000 !important;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- CABECERA IDÉNTICA A HOSPEDAJES.PHP -->
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h3 class="mb-0 text-black fw-bold" style="font-size: 1.25rem;">GESTIÓN TURNO ACTUAL</h3>
            <span class="text-cajero-info uppercase">
                CAJERO: <?= $cajero_seleccionado ?>
                &nbsp;|&nbsp;
                APERTURA: <?= $inicio_turno_caja ?>
            </span>
        </div>

        <div class="card-body">
            <!-- SELECTOR INTEGRADO EN EL GRID SUPERIOR DE FORMA MODERNA Y SENCILLA -->
            <?php if ($es_admin && count($cajas_abiertas) > 1): ?>
                <div class="form-group row align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="small fw-bold text-black text-uppercase">Cajero en Turno:</label>
                        <select class="form-control form-control-sm"
                            onchange="window.location.href='turno.php?cajaID='+this.value">
                            <?php foreach ($cajas_abiertas as $c): ?>
                                <option value="<?= $c['cajaID'] ?>" <?= ($c['cajaID'] == $caja_seleccionada) ? 'selected' : '' ?>>
                                    <?= mb_strtoupper($c['nombre_usuario']) ?>
                                    (<?= date('d/m H:i', strtotime($c['fecha_apertura'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <!-- TABLA MINIMALISTA -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Tipo</th>
                            <th scope="col">Concepto de Operación</th>
                            <th scope="col">Forma de Pago</th>
                            <th scope="col">Cajero</th>
                            <th scope="col">Fecha y Hora</th>
                            <th scope="col" class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($movimientos_caja) > 0): ?>
                            <?php foreach ($movimientos_caja as $mov): ?>
                                <tr>
                                    <td>
                                        <?php if ($mov['tipo'] === 'INGRESO'): ?>
                                            <span class="color-ingreso"><i class="fas fa-arrow-down"></i> INGRESO</span>
                                        <?php else: ?>
                                            <span class="color-egreso"><i class="fas fa-arrow-up"></i> EGRESO</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-uppercase">
                                        <?= htmlspecialchars($mov['descripcion']) ?>
                                        <?php if (!empty($mov['detalle'])): ?>
                                            <br><small class="text-muted text-lowercase" style="font-style: italic;">
                                                <?= htmlspecialchars($mov['detalle']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-uppercase font-weight-bold text-secondary">
                                        <?= mb_strtoupper($mov['forma_pago']) ?>
                                    </td>
                                    <td>
                                        <?= mb_strtoupper($cajero_seleccionado) ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?= date('d/m/Y H:i', strtotime($mov['fecha_registro'])) ?>
                                    </td>
                                    <td
                                        class="text-right font-weight-bold <?= ($mov['tipo'] === 'INGRESO') ? 'color-ingreso' : 'color-egreso' ?>">
                                        <?= ($mov['tipo'] === 'INGRESO' ? '+' : '-') ?>
                                        <?= number_format($mov['monto'], 2, '.', ',') ?> Bs.
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No existen movimientos registrados en este turno.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="border-top">
                        <?php foreach ($desglose_pagos as $metodo => $valores):
                            $saldo_metodo = $valores['ingresos'] - $valores['egresos'];
                            if ($saldo_metodo == 0)
                                continue;
                            ?>
                            <tr class="total-fila-metodo text-secondary">
                                <th colspan="5" class="text-right font-weight-normal">
                                    Líquido en <?= $metodo ?>:
                                </th>
                                <th class="text-right font-weight-bold text-dark">
                                    <?= number_format($saldo_metodo, 2, '.', ',') ?> Bs.
                                </th>
                            </tr>
                        <?php endforeach; ?>

                        <tr class="total-fila-neta">
                            <th colspan="5" class="text-right">
                                SALDO NETO DEL TURNO:
                            </th>
                            <th class="text-right">
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