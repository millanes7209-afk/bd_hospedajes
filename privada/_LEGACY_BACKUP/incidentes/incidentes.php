<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los datos de los incidentes
$sql = $db->Prepare("SELECT inc.incidenteID, cli.clienteID,cli.ci as cedula, inc.descripcion, inc.fecha, inc._estado,
                            CONCAT_WS(' ', cli.nombres, cli.apellidos) as cliente
                     FROM incidentes inc
                     INNER JOIN clientes cli ON inc.clienteID = cli.clienteID
                     ORDER BY inc.incidenteID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Incidentes</title>
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
            <h3>GESTIÓN DE INCIDENTES</h3>
        </div>
        
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="incidente_nuevo.php" class="btn btn-success" role="button">Añadir Incidente</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Cedula</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($rs) : ?>
                    <?php $b = 1; ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $fila['cedula']; ?></td>
                            <td><?php echo $fila['cliente']; ?></td>
                            <td><?php echo htmlspecialchars($fila['descripcion']); ?></td>
                            <td><?php echo $fila['fecha']; ?></td>
                            <td><?php echo $fila['_estado']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['incidenteID']; ?>" method="post" action="incidente_modificar.php" style="display:inline;">
                                    <input type="hidden" name="incidenteID" value="<?php echo $fila['incidenteID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['incidenteID']; ?>" method="post" action="incidente_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="incidenteID" value="<?php echo $fila['incidenteID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea eliminar este incidente?');">Eliminar</button>
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
