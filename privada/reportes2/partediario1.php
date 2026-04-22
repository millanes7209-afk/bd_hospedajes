<?php
session_start();
require_once("../../conexion.php");

$fecha1 = $_REQUEST["fecha1"];
$fecha2 = $_REQUEST["fecha2"];

$sql = $db->Prepare("
    SELECT 
        CONCAT_WS(' ', p.nombres, p.apellidos) AS cliente, 
        h.numero AS habitacion,
        FLOOR(DATEDIFF(CURDATE(), p.fecha_nacimiento) / 365) AS edad,
        p.est_civil AS estado_civil, 
        p.profesion, 
        p.lugar_nacimiento AS procedencia,
        hsp.checkin AS fecha_checkin, 
        p.ci AS documento
    FROM 
        hospedajes hsp
    JOIN 
        hospedajes_clientes hc ON hsp.hospedajeID = hc.hospedajeID
    JOIN 
        clientes p ON hc.clienteID = p.clienteID
    JOIN 
        habitaciones h ON hsp.habitacionID = h.habitacionID
    WHERE 
        DATE(hsp.checkin) BETWEEN ? AND ?
        AND hsp._estado <> 'X'
    ORDER BY 
        hsp.checkin
");
$rs = $db->GetAll($sql, array($fecha1, $fecha2));

$sql1 = $db->Prepare("SELECT * FROM vista_empresa");
$rs1 = $db->GetAll($sql1);
$nombre = $rs1[0]["nombre"];
$logo_agencia = $rs1[0]["logo_agencia"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Hospedajes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 10px;
            white-space: nowrap;
        }
        th {
            background-color: #f2f2f2;
        }
        h1, p {
            text-align: center;
        }
        .logo {
            width: 20%;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .center {
            text-align: center;
        }
        @media print {
            .btn {
                display: none;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <table>
            <tr>
                <td><img src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/unibol/img/<?php echo $logo_agencia; ?>" class="logo"></td>
                <td class="center">
                    <h1>Reporte de Hospedajes</h1>
                    <p><b>Del:</b> <?php echo date("d/m/y", strtotime($fecha1)); ?> <b>Al:</b> <?php echo date("d/m/y", strtotime($fecha2)); ?></p>
                </td>
            </tr>
        </table>
        <br>
        <?php if ($rs): ?>
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Hab</th>
                        <th>Edad</th>
                        <th>E. Civil</th>
                        <th>Profesión</th>
                        <th>Procedencia</th>
                        <th>Ingreso</th>
                        <th>Documento</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rs as $fila): ?>
                        <tr>
                            <td><?php echo ucwords(strtolower($fila['cliente'])); ?></td>
                            <td><?php echo $fila['habitacion']; ?></td>
                            <td><?php echo $fila['edad']; ?></td>
                            <td><?php echo ucwords(strtolower($fila['estado_civil'])); ?></td>
                            <td><?php echo ucwords(strtolower($fila['profesion'])); ?></td>
                            <td><?php echo ucwords(strtolower($fila['procedencia'])); ?></td>
                            <td><?php echo date('d/m/y', strtotime($fila['fecha_checkin'])); ?></td>
                            <td><?php echo $fila['documento']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="center">No se encontraron registros en el rango de fechas seleccionado.</p>
        <?php endif; ?>
    </div>
    <div class="text-center mt-4">
    <a href="javascript:window.print()" class="btn btn-primary">Imprimir Reporte</a>
    <a href="javascript:window.close()" class="btn btn-secondary">Cerrar Ventana</a>
</div>

</body>
</html>
