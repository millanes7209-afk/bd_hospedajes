<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// Consulta SQL para obtener las habitaciones
$sql = $db->Prepare("SELECT numero, descripcion
                     FROM habitaciones
                     WHERE _estado <> 'X'
                     AND   estado='MANTENIMIENTO'
                     ORDER BY habitacionID ASC");
$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Habitaciones</title>
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
    <div class="col-md-8">
    <div class="card">
        <div class="card-header">
           <h3>GESTIÓN HABITACIONES</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Número</th>
                            <th scope="col">Descripcion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                        <?php 
                        $b = 1;
                        ?>
                        <?php foreach ($rs as $fila) : ?>
                            <tr>
                                <td><?php echo $b; ?></td>
                                <td><?php echo $fila['numero']; ?></td>
                                <td><?php echo $fila['descripcion']; ?></td>
                            </tr>
                        <?php $b++; ?>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</body>
</html>
