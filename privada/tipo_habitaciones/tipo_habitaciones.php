<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta SQL corregida para obtener los tipos de habitaciones
$sql = $db->Prepare("   SELECT  *, tipohabitacionID
                        FROM    tipo_habitaciones
                        WHERE   _estado <> 'X'
                        ORDER BY tipohabitacionID ASC
");

// Ejecutar la consulta
$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Tipos de Habitaciones</title>
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
           <h3>GESTIÓN DE TIPOS DE HABITACIONES</h3>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="tipo_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Tipo de Habitación</a>
          </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Descripción</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php 
                    $b = 1; // Contador para el número de fila
                    ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $b; ?></td>
                            <td><?php echo $fila['tipo']; ?></td>
                            <td><?php echo $fila['precio']; ?></td>
                            <td><?php echo $fila['descripcion']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['tipohabitacionID']; ?>" method="post" action="tipo_modificar.php" style="display:inline;">
                                    <input type="hidden" name="tipohabitacionID" value="<?php echo $fila['tipohabitacionID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['tipohabitacionID']; ?>" method="post" action="tipo_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="tipohabitacionID" value="<?php echo $fila['tipohabitacionID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea realmente eliminar el tipo de habitación <?php echo $fila['tipo']; ?>?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php $b++; ?>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
