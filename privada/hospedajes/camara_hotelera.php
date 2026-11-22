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
            h.hospedajeID,
            h.estado,
            h.checkin, 
            h.checkout,
            hab.numero AS habitacion_numero,
            c.apellido1,
            c.apellido2,
            c.nombres,
            c.ci,
            ps.nombre AS nacionalidad,
            c.fecha_nacimiento,
            c.estado_civil,
            c.profesion,
            c.lugar_nacimiento
        FROM hospedajes h
        JOIN hospedajes_clientes ch ON h.hospedajeID = ch.hospedajeID
        JOIN clientes c ON ch.clienteID = c.clienteID
        LEFT JOIN paises ps ON c.paisID = ps.paisID
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        WHERE h._estado <> 'X' AND ch._estado <> 'X' AND c._estado <> 'X'
        AND DATE(h.checkin) BETWEEN ? AND ? 
        AND h.empresaID = ?
        ORDER BY h.checkin DESC, h.hospedajeID DESC, ch.hospedajeclienteID ASC";

$rs = $db->obtenerTodo($sql, [$fecha_inicio, $fecha_fin, $_SESSION['empresaID']]);

// Agrupar huéspedes por hospedaje
$hospedajes = [];
if ($rs) {
    foreach ($rs as $row) {
        $hid = $row['hospedajeID'];
        if (!isset($hospedajes[$hid])) {
            $hospedajes[$hid] = [
                'checkin'           => $row['checkin'],
                'checkout'          => $row['checkout'],
                'habitacion_numero' => $row['habitacion_numero'],
                'huespedes'         => []
            ];
        }
        $hospedajes[$hid]['huespedes'][] = $row;
    }
}
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
                        <?php if (!empty($hospedajes)) : ?>
                            <?php foreach ($hospedajes as $hosp) : 
                                $huespedes = $hosp['huespedes'];
                                $numHuespedes = count($huespedes);
                                $rowspanAttr = ($numHuespedes > 1) ? " rowspan='{$numHuespedes}'" : "";
                            ?>
                                <?php foreach ($huespedes as $index => $huesped) : ?>
                                    <tr>
                                        <?php if ($index === 0) : ?>
                                            <td<?= $rowspanAttr ?> style="vertical-align: middle; text-align: center;"><?= date('d/m/Y', strtotime($hosp['checkin'])) ?></td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($huesped['apellido1'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($huesped['apellido2'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($huesped['nombres'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($huesped['ci'] ?? '') ?></td>
                                        <?php if ($index === 0) : ?>
                                            <td<?= $rowspanAttr ?> style="vertical-align: middle; text-align: center;"><?= htmlspecialchars($hosp['habitacion_numero'] ?? '') ?></td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($huesped['nacionalidad'] ?? '') ?></td>
                                        <td style="text-align: center;">
                                            <?= (!empty($huesped['fecha_nacimiento']) && $huesped['fecha_nacimiento'] !== '0000-00-00') ? date('d/m/Y', strtotime($huesped['fecha_nacimiento'])) : '' ?>
                                        </td>
                                        <td><?= htmlspecialchars($huesped['estado_civil'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($huesped['profesion'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($huesped['lugar_nacimiento'] ?? '') ?></td>
                                        <?php if ($index === 0) : ?>
                                            <td<?= $rowspanAttr ?> style="vertical-align: middle; text-align: center;">
                                                <?= (!empty($hosp['checkout']) ? date('d/m/Y', strtotime($hosp['checkout'])) : '-') ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
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