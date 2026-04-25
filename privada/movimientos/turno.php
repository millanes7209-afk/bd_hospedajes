<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Extraer entorno seguro de sesión
$empresaID = $_SESSION['empresaID'] ?? null;
$caja_abierta_id = $_SESSION['caja_abierta_id'] ?? null;
$usuarioActual = $_SESSION["sesion_usuario"] ?? 'Cajero';

// Detener si no hay caja abierta
if (!$caja_abierta_id) {
    echo "<div class='container mt-5'><div class='alert alert-danger shadow-sm border-left-danger'>
            <h4 class='alert-heading'><i class='fas fa-exclamation-triangle'></i> Acceso Denegado</h4>
            <p><strong>No existe una caja abierta en su sesión actualmente.</strong><br>
            Para poder visualizar los ingresos y egresos de un turno, primero debe realizar el proceso de 'Abrir Caja'.</p>
          </div></div></body></html>";
    exit;
}

// 1. Obtención ESTRICA de la base de datos (SÓLO la caja actual identificada)
$sql = "SELECT 
            m.movimientoID,
            m.tipo,
            m.concepto AS descripcion,
            m.detalle,
            fp.tipo AS forma_pago,
            m.monto,
            m._fec_insercion AS fecha_registro
        FROM movimientos m
        INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
        WHERE m.cajaID = ? AND m.empresaID = ? AND m._estado = 'A'
        ORDER BY m.movimientoID DESC";

$movimientos_caja = $db->obtenerTodo($sql, [$caja_abierta_id, $empresaID]);

if (!$movimientos_caja) {
    $movimientos_caja = []; // Fallback por si no hay registros aún
}

// 2. Calcular los totales líquidos del turno matemáticamente
$total_ingresos = 0;
$total_egresos = 0;

foreach ($movimientos_caja as $mov) {
    if ($mov['tipo'] === 'INGRESO') {
        $total_ingresos += (float)$mov['monto'];
    } elseif ($mov['tipo'] === 'EGRESO') {
        $total_egresos += (float)$mov['monto'];
    }
}

$saldo_final = $total_ingresos - $total_egresos;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Movimientos del Turno - Sistema Web Dulces Sueños</title>
    <!-- Los estilos bootstrap nativos ya son provistos por libreria_menu.php -->
    <style>
        thead {
            color: black!important;
            background: #b5b5b5!important;
        }
        .card {
            margin: 20px;
        }
        .tabla-turno th, .tabla-turno td { vertical-align: middle !important; }
        .badge-ingreso { background-color: #28a745; color: white; padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; letter-spacing: 0.5px;}
        .badge-egreso { background-color: #dc3545; color: white; padding: 5px 12px; border-radius: 4px; font-weight: bold; font-size: 0.85rem; letter-spacing: 0.5px;}
        .td-concepto { font-size: 0.95rem; font-weight: 500; }
        .monto-td { font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h3 class="mb-0 m-0" style="font-size: 1.4rem;">
                    <i class="fas fa-cash-register mr-2"></i> Flujo Financiero del Turno 
                </h3>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped tabla-turno border-bottom mb-0">
                        <thead>
                            <tr class="">
                                <th width="15%">TIPO</th>
                                <th width="35%">CONCEPTO DE OPERACIÓN</th>
                                <th width="15%">FORMA DE PAGO</th>
                                <th width="15%">CAJERO</th>
                                <th width="10%">FECHA Y HORA</th>
                                <th width="10%">MONTO (Bs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($movimientos_caja) > 0): ?>
                                <?php foreach ($movimientos_caja as $mov): ?>
                                    <tr>
                                        <td class="">
                                            <?php if($mov['tipo'] === 'INGRESO'): ?>
                                                <span class="badge-ingreso"><i class="fas fa-plus-circle"></i> INGRESO</span>
                                            <?php else: ?>
                                                <span class="badge-egreso"><i class="fas fa-minus-circle"></i> EGRESO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="td-concepto text-dark text-uppercase">
                                            <?php echo htmlspecialchars($mov['descripcion']); ?>
                                            <?php if (!empty($mov['detalle'])): ?>
                                                <br><small class="text-muted text-lowercase" style="font-style: italic;"><i class="fas fa-comment-dots text-secondary"></i> <?php echo htmlspecialchars($mov['detalle']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class=" text-secondary font-weight-bold">
                                            <i class="fas fa-money-bill-wave"></i> <?php echo mb_strtoupper($mov['forma_pago']); ?>
                                        </td>
                                        <td class=" text-secondary">
                                            <i class="fas fa-user"></i> <?php echo mb_strtoupper($usuarioActual); ?>
                                        </td>
                                        <td class=" text-muted small">
                                            <?php echo date('d/m/Y H:i', strtotime($mov['fecha_registro'])); ?>
                                        </td>
                                        <td class="text-right font-weight-bold monto-td <?php echo ($mov['tipo'] === 'INGRESO') ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ($mov['tipo'] === 'INGRESO' ? '+' : '-'); ?>
                                            <?php echo number_format($mov['monto'], 2, '.', ','); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-folder-open mb-3" style="font-size: 3rem; opacity: 0.5;"></i><br>
                                        <h5 class="font-weight-light">El turno actual está vacío.</h5>
                                        <p class="mb-0">No existen movimientos registrados en la Caja #<?php echo $caja_abierta_id; ?>.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-light border-top">
                            <tr>
                                <th colspan="5" class="text-right align-middle text-muted">TOTAL INGRESOS:</th>
                                <th class="text-right font-weight-bold">
                                    <?php echo number_format($total_ingresos, 2, '.', ','); ?> Bs.
                                </th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right align-middle text-muted">TOTAL EGRESOS / GASTOS:</th>
                                <th class="text-right font-weight-bold">
                                    - <?php echo number_format($total_egresos, 2, '.', ','); ?> Bs.
                                </th>
                            </tr>
                            <tr class="bg-white">
                                <th colspan="5" class="text-right align-middle font-weight-bold">SALDO LÍQUIDO DEL TURNO:</th>
                                <th class="text-right font-weight-bold" style="border-top: 2px solid #000; font-size: 1.1rem;">
                                    <?php echo number_format($saldo_final, 2, '.', ','); ?> Bs.
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
