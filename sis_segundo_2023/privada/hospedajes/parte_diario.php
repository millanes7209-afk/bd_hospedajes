<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
require_once("utils/hospedajes_utilidades.php");

verificarSesion();

// Parámetros de filtrado
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$empresaID = $_SESSION['empresaID'];

// CONSULTA OPTIMIZADA: Sin usuarios, filtrada por empresa y estados activos
$sql = "SELECT 
            h.hospedajeID,
            h.estado,
            h.checkin,
            h.checkout,
            hab.numero AS habitacion_numero,
            GROUP_CONCAT(CONCAT_WS(' ', c.apellido1, c.apellido2, c.nombres) SEPARATOR '[NEXT]') as clientes_nombres,
            GROUP_CONCAT(c.ci SEPARATOR '[NEXT]') as clientes_ci,
            GROUP_CONCAT(c.fecha_nacimiento SEPARATOR '|') as clientes_fechas_nac,
            GROUP_CONCAT(c.estado_civil SEPARATOR '|') as clientes_estados_civiles,
            GROUP_CONCAT(c.profesion SEPARATOR '|') as clientes_profesiones,
            GROUP_CONCAT(c.lugar_nacimiento SEPARATOR '|') as clientes_procedencias
        FROM hospedajes h
        JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
        JOIN clientes c ON hc.clienteID = c.clienteID
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        WHERE h._estado <> 'X'
          AND hc._estado <> 'X'
          AND c._estado <> 'X'
          AND h.empresaID = ?
          AND DATE(h.checkin) BETWEEN ? AND ?
        GROUP BY h.hospedajeID
        ORDER BY hab.numero ASC";

$hospedajes_dia = $db->obtenerTodo($sql, [$empresaID, $fecha_inicio, $fecha_fin]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parte Diario</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <?= generarEncabezadoImpresion('PARTE DIARIO DE HOSPEDAJES', $fecha_inicio, $fecha_fin) ?>

        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h3>Parte Diario: <?= date('d/m/Y', strtotime($fecha_inicio)) ?> - <?= date('d/m/Y', strtotime($fecha_fin)) ?></h3>
            <div class="d-flex gap-2">
                <form class="d-flex gap-2" method="GET">
                    <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>" placeholder="Fecha Inicio">
                    <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>" placeholder="Fecha Fin">
                    <button type="submit" class="btn btn-dark">Consultar</button>
                </form>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Hab</th>
                        <th>Cliente(s)</th>
                        <th>CI</th>
                        <th>Edad</th>
                        <th>Estado Civil</th>
                        <th>Profesión</th>
                        <th>Procedencia</th>
                        <th>Fecha Ingreso</th>
                        <th>Fecha Salida</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($hospedajes_dia): ?>
                        <?php foreach ($hospedajes_dia as $h): ?>
                            <tr>
                                <td style="text-align: center;"><?= htmlspecialchars($h['habitacion_numero']) ?></td>
                                <td>- <?= str_replace('[NEXT]', '<br>- ', htmlspecialchars($h['clientes_nombres'])) ?></td>
                                <td>- <?= str_replace('[NEXT]', '<br>- ', htmlspecialchars($h['clientes_ci'])) ?></td>
                                <td>- <?= calcularEdad($h['clientes_fechas_nac'], '<br>- ') ?></td>
                                <td>- <?= str_replace('|', '<br>- ', htmlspecialchars($h['clientes_estados_civiles'])) ?></td>
                                <td>- <?= str_replace('|', '<br>- ', htmlspecialchars($h['clientes_profesiones'])) ?></td>
                                <td>- <?= str_replace('|', '<br>- ', htmlspecialchars($h['clientes_procedencias'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($h['checkin'])) ?></td>
                                <td><?= $h['checkout'] ? date('d/m/Y H:i', strtotime($h['checkout'])) : '-' ?></td>
                                <td style="text-align: center;"><?= ($h['estado'] === 'ACTIVO' || $h['estado'] === 'ABIERTO') ? 'A' : 'I' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">No existen registros para el rango de fechas seleccionado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>