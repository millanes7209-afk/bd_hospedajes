<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los datos de las visitas
$sql = $db->Prepare("SELECT visitaID, clienteID, fecha
                     FROM visitas
                     ORDER BY visitaID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Visitas</title>
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
        .btn-accion {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN DE VISITAS</h3>
        </div>
        
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="visita_nueva.php" class="btn btn-success" role="button">Añadir Visita</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Cliente ID</th>
                            <th scope="col">Fecha</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php $b = 1; ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $b; ?></td>
                            <td><?php echo $fila['clienteID']; ?></td>
                            <td><?php echo $fila['fecha']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['visitaID']; ?>" method="post" action="visita_modificar.php" style="display:inline;">
                                    <input type="hidden" name="visitaID" value="<?php echo $fila['visitaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['visitaID']; ?>" method="post" action="visita_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="visitaID" value="<?php echo $fila['visitaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea eliminar esta visita?');">Eliminar</button>
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
