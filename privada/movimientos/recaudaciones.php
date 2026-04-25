<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Seguridad básica
if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}

$empresaID = $_SESSION['empresaID'];
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Consulta de Historial de Recaudaciones
$sql = "SELECT r.*, 
               u1.usuario as recepcionista, 
               u2.usuario as propietario
        FROM recaudaciones r
        INNER JOIN usuarios u1 ON r.usuariorecepcionistaID = u1.usuarioID
        INNER JOIN usuarios u2 ON r.usuariopropietarioID = u2.usuarioID
        WHERE r.empresaID = ? 
          AND DATE(r.fecha) BETWEEN ? AND ?
          AND r._estado <> 'X'
        ORDER BY r.fecha DESC";

$recaudaciones = $db->obtenerTodo($sql, [$empresaID, $fecha_inicio, $fecha_fin]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Recaudaciones</title>
        @media print {
            body { background: white !important; padding: 0 !important; margin: 0 !important; }
            #sidebar-wrapper, .sidebar, nav, .navbar, .btn, .filtros, #sideNav, .modal { display: none !important; }
            #page-content-wrapper { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            .card { margin: 0; box-shadow: none; border: none; }
            .container-fluid { width: 100% !important; padding: 0 !important; }
            .table { font-size: 11px; width: 100% !important; border-collapse: collapse !important; }
            .table th, .table td { border: 1px solid #ddd !important; }
            .card-header h3 { font-size: 16px !important; }
        }
    </style>
<style>
    thead {
        color: black !important;
        background: #b5b5b5 !important;
    }
    .card {
        margin: 20px;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        border: 0 !important;
    }
    .tabla-turno th, .tabla-turno td { vertical-align: middle !important; }
</style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h3 class="mb-0 m-0" style="font-size: 1.4rem;">
                <i class="fas fa-file-invoice-dollar mr-2"></i> HISTORIAL DE RECAUDACIONES (DINERO ENTREGADO)
            </h3>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fas fa-print"></i> IMPRIMIR REPORTE</button>
        </div>
        <div class="card-body">
            <form class="row mb-4 filtros">
                <div class="col-md-3">
                    <label>Desde:</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
                </div>
                <div class="col-md-3">
                    <label>Hasta:</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped tabla-turno border-bottom mb-0">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Fecha/Hora</th>
                            <th>Nro Comprobante</th>
                            <th>Recepcionista (Entregó)</th>
                            <th>Propietario (Recibió)</th>
                            <th class="text-end">Monto Recaudado</th>
                            <th width="5%" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!$recaudaciones): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay registros de recaudación en este rango.</td></tr>
                        <?php endif; ?>
                        <?php 
                        $total_general = 0;
                        foreach ($recaudaciones as $r): 
                            $total_general += $r['monto'];
                        ?>
                            <tr>
                                <td class="text-center"><?= $r['recaudacionID'] ?></td>
                                <td class="text-center"><?= date('d/m/Y H:i', strtotime($r['fecha'])) ?></td>
                                <td class="text-center fw-bold"><?= $r['comprobante_nro'] ?></td>
                                <td><?= $r['recepcionista'] ?></td>
                                <td><?= $r['propietario'] ?></td>
                                <td class="text-end fw-bold">Bs. <?= number_format($r['monto'], 2) ?></td>
                                <td class="text-center align-middle" style="width: 40px; border-left: 1px solid #dee2e6;">
                                    <button class="btn btn-sm btn-outline-secondary border-0" onclick="verDetalleRecaudacion(<?= $r['recaudacionID'] ?>)" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <?php if ($recaudaciones): ?>
                    <tfoot class="bg-light border-top">
                        <tr>
                            <th colspan="5" class="text-right align-middle text-muted" style="text-align: right;">TOTAL RECAUDADO EN EL PERIODO:</th>
                            <th colspan="2" class="text-start" style="font-size: 1.1rem; border-left: 1px solid #dee2e6;">Bs. <?= number_format($total_general, 2) ?></th>
                        </tr>
                    </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Auditoría de Recaudación -->
<div class="modal fade" id="modalDetalleRecaudacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-receipt me-2"></i> DETALLE DE RECAUDACIÓN</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalRecaudacionContenido">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando desglose de la entrega...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CERRAR</button>
            </div>
        </div>
    </div>
</div>

<script>
    const modalRecaudacion = new bootstrap.Modal(document.getElementById('modalDetalleRecaudacion'));

    function verDetalleRecaudacion(recaudacionID) {
        const contenedor = document.getElementById('modalRecaudacionContenido');
        contenedor.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Cargando desglose de la entrega...</p></div>';
        modalRecaudacion.show();

        fetch('ajax_detalle_recaudacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'recaudacionID=' + recaudacionID
        })
        .then(r => r.text())
        .then(html => {
            contenedor.innerHTML = html;
        })
        .catch(err => {
            contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión al cargar los detalles.</div>';
        });
    }
</script>
</body>
</html>
