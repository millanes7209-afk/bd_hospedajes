<?php
session_start();
require_once '../../conexion.php';
require_once '../../libreria_menu.php';
require_once 'utils/hospedajes_utilidades.php';

verificarSesion();

// Parámetros iniciales
$sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['empresaID'] ?? 1;
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Consulta filtrada por el rango de fechas seleccionado
$sql = "SELECT 
            h.hospedajeID, h.estado, h.checkin, h.checkout,
            GROUP_CONCAT(c.apellido1 SEPARATOR '[NEXT]') as paternos,
            GROUP_CONCAT(c.apellido2 SEPARATOR '[NEXT]') as maternos,
            GROUP_CONCAT(c.nombres SEPARATOR '[NEXT]') as nombres_ind,
            GROUP_CONCAT(c.ci SEPARATOR '[NEXT]') as cis,
            GROUP_CONCAT(ps.nombre SEPARATOR '[NEXT]') as nacionalidades,
            GROUP_CONCAT(c.fecha_nacimiento SEPARATOR '[NEXT]') as fechas_nac,
            GROUP_CONCAT(c.estado_civil SEPARATOR '[NEXT]') as estados_civiles,
            GROUP_CONCAT(c.profesion SEPARATOR '[NEXT]') as profesiones,
            GROUP_CONCAT(c.lugar_nacimiento SEPARATOR '[NEXT]') as procedencias,
            hab.numero AS habitacion_numero
        FROM hospedajes h
        JOIN hospedajes_clientes ch ON h.hospedajeID = ch.hospedajeID
        JOIN clientes c ON ch.clienteID = c.clienteID
        LEFT JOIN paises ps ON c.paisID = ps.paisID
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        WHERE h._estado <> 'X' AND ch._estado <> 'X' AND c._estado <> 'X'
        AND DATE(h.checkin) BETWEEN ? AND ? 
        AND h.empresaID = ?
        GROUP BY h.hospedajeID
        ORDER BY h.checkin DESC";

$rs = $db->obtenerTodo($sql, [$fecha_inicio, $fecha_fin, $_SESSION['empresaID']]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cámara Hotelera - Gestión</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
</head>
<body>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>CÁMARA HOTELERA</h3>
            <a href="exportar_excel.php?fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>&sucursal_id=<?= $_SESSION['empresaID'] ?>" class="btn btn-success">
                Exportar Excel (.xls)
            </a>
        </div>
    
        <div class="card-body">
            <form method="GET" action="" class="form-inline mb-4">
                <input type="hidden" name="sucursal_id" value="<?= $_SESSION['empresaID'] ?>">
                <div class="row w-100">
                    <div class="col-md-4">
                        <label>Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control w-100" value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-4">
                        <label>Fecha Fin:</label>
                        <input type="date" name="fecha_fin" class="form-control w-100" value="<?= $fecha_fin ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Consultar Rango</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped" id="tablaCamara">
                    <thead>
                        <tr>
                            <th>Ingreso</th>
                            <th>Paterno</th>
                            <th>Materno</th>
                            <th>Nombre</th>
                            <th>C.I.</th>
                            <th>Hab</th>
                            <th>Nacionalidad</th>
                            <th>F. Nac.</th>
                            <th>E. Civil</th>
                            <th>Profesión</th>
                            <th>Procedencia</th>
                            <th>Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                            <?php foreach ($rs as $fila) : ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($fila['checkin'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['paternos'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['maternos'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['nombres_ind'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['cis'])) ?></td>
                                    <td style="text-align: center;"><?= $fila['habitacion_numero'] ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['nacionalidades'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['fechas_nac'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['estados_civiles'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['profesiones'])) ?></td>
                                    <td><?= str_replace('[NEXT]', '<br>', htmlspecialchars($fila['procedencias'])) ?></td>
                                    <td><?= $fila['checkout'] ? date('d/m/Y', strtotime($fila['checkout'])) : '-' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr><td colspan="12" class="text-center">No hay registros en este rango de fechas</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>