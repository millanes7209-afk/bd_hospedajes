<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

/**
 * PANEL DE AUDITORÍA Y FISCALIZACIÓN
 * Permite al administrador revisar cambios de precio, eliminaciones y barajado de pagos.
 */

$empresaID = $_SESSION["empresaID"] ?? null;
if (!$empresaID) die("Error de acceso.");

$verHistorial = isset($_GET['historial']) && $_GET['historial'] == '1';
$estadoBusqueda = $verHistorial ? 1 : 0;

$sql = "SELECT a.*, u.usuario, h.hospedajeID 
        FROM auditorias a
        INNER JOIN usuarios u ON a.usuarioID = u.usuarioID
        LEFT JOIN hospedajes h ON a.hospedajeID = h.hospedajeID
        WHERE a.empresaID = ? AND a.estado_revision = ?
        ORDER BY a.fecha DESC";

$auditorias = $db->obtenerTodo($sql, [$empresaID, $estadoBusqueda]);

// Función auxiliar para mostrar el detalle de pagos de forma legible
function formatearDetalle($json) {
    if (!$json || $json == 'CANCELADO') return "<span class='text-muted'>$json</span>";
    $datos = json_decode($json, true);
    if (!$datos) return $json;
    
    $html = "<ul class='list-unstyled mb-0 small'>";
    foreach ($datos as $item) {
        $html .= "<li><strong>{$item['tipo']}:</strong> Bs. {$item['monto']}</li>";
    }
    $html .= "</ul>";
    return $html;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Auditoría Financiera</title>
    <style>
        .badge-modificacion { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-eliminacion { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .table-hover tbody tr:hover { background-color: rgba(0,0,0,.03); }
        .card-header-flex { display: flex; justify-content: space-between; align-items: center; }
        .diff-container { font-family: 'Courier New', Courier, monospace; font-size: 0.85rem; }
        .diff-old { background-color: #ffdce0; }
        .diff-new { background-color: #cdffd8; }
        body { color: #000 !important; }
    </style>
</head>
<body>
    <div class="container mt-3 mb-5">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white card-header-flex">
                <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i> PANEL DE FISCALIZACIÓN</h4>
                <div>
                    <?php if ($verHistorial): ?>
                        <a href="hospedajes_auditoria.php" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-exclamation-circle"></i> Ver Pendientes
                        </a>
                    <?php else: ?>
                        <a href="hospedajes_auditoria.php?historial=1" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-success py-2"><?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Tipo / ID</th>
                                <th>Cambio en Total (Bs)</th>
                                <th>Detalle de Distribución</th>
                                <th>Justificación del Usuario</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$auditorias) : ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No se encontraron registros de auditoría.</td>
                                </tr>
                            <?php endif; ?>

                            <?php foreach ($auditorias as $a) : ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo date('d/m/Y', strtotime($a['fecha'])); ?></div>
                                        <div class="small text-muted"><?php echo date('H:i', strtotime($a['fecha'])); ?> hrs.</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold fs-6"><i class="fas fa-user-circle me-1 text-primary"></i><?php echo $a['usuario']; ?></div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($a['tipo_auditoria'] == 'ELIMINACION' ? 'badge-eliminacion' : 'badge-modificacion'); ?> px-2 py-1 small">
                                            <?php echo $a['tipo_auditoria']; ?>
                                        </span>
                                        <div class="small fw-bold mt-1">Hospedaje #<?php echo $a['hospedajeID']; ?></div>
                                    </td>
                                    <td>
                                        <div class="small">Original: <strong><?php echo $a['monto_anterior']; ?></strong></div>
                                        <div class="small">Nuevo: <strong class="text-primary"><?php echo $a['monto_nuevo']; ?></strong></div>
                                    </td>
                                    <td class="diff-container">
                                        <div class="p-1 mb-1 diff-old border rounded">
                                            <div class="fw-bold x-small text-uppercase" style="font-size: 0.65rem;">Anterior:</div>
                                            <?php echo formatearDetalle($a['detalle_original']); ?>
                                        </div>
                                        <div class="p-1 diff-new border rounded text-dark">
                                            <div class="fw-bold x-small text-uppercase" style="font-size: 0.65rem;">Nuevo:</div>
                                            <?php echo formatearDetalle($a['detalle_nuevo']); ?>
                                        </div>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="p-2 bg-light border rounded small italic">
                                            "<?php echo htmlspecialchars($a['motivo']); ?>"
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($a['estado_revision'] == 0): ?>
                                            <form action="procesar_auditoria.php" method="post" onsubmit="return confirm('¿Marcar este registro como revisado?')">
                                                <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Revisado
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success"><i class="fas fa-check-double"></i> Fiscalizado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
