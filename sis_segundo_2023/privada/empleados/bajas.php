<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Consulta exclusiva de empleados con estado_laboral INACTIVO
$sql = "SELECT e.*, ee.sueldo, ee.fecha_inicio, ee.fecha_fin, ee.rolID, ee.estado_laboral,
               r.rol AS cargo,
               CONCAT_WS(' ', e.apellidos, e.nombres) AS empleado
        FROM empleados e
        INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
        INNER JOIN roles r ON ee.rolID = r.rolID
        WHERE e._estado <> 'X'
        AND ee.empresaID = ?
        AND ee.estado_laboral = 'INACTIVO'
        ORDER BY ee.fecha_fin DESC";
$rs = $db->obtenerTodo($sql, array($empresaID));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial de Bajas - Dulces Sueños</title>
    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }

        .card {
            margin: 20px;
        }

        tr {
            color: black;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>HISTORIAL DE BAJAS LABORALES</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                            <tr>
                                <th>N°</th>
                                <th>C.I.</th>
                                <th>Empleado</th>
                                <th>Último Cargo</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin Contrato</th>
                                <th>Sueldo</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs): ?>
                            <?php $b = 1;
                            foreach ($rs as $fila): ?>
                                    <tr>
                                        <td><?php echo $b++; ?></td>
                                        <td><?php echo $fila['ci']; ?></td>
                                        <td><?php echo $fila['empleado']; ?></td>
                                        <td><?php echo htmlspecialchars($fila['cargo']); ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($fila['fecha_inicio'])); ?></td>
                                        <td><?php echo !empty($fila['fecha_fin']) ? date("d/m/Y", strtotime($fila['fecha_fin'])) : '-'; ?></td>
                                        <td><?php echo number_format($fila['sueldo'], 2, ',', '.'); ?></td>
                                    </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No hay registros de bajas.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>