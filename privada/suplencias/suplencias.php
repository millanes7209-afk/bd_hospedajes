<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// Consulta para obtener los datos de las suplencias
$sql = $db->Prepare("SELECT suplenciaID, turnoID, id_persona, id_usuario, aceptado, fecha_aceptacion, _estado
                     FROM suplencias
                     WHERE _estado <> 'X' 
                     ORDER BY suplenciaID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Suplencias</title>
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
            <h3>GESTIÓN SUPLENCIAS</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="suplencia_nuevo.php" class="btn btn-success" role="button">Añadir Suplencia</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Turno ID</th>
                            <th scope="col">Persona ID</th>
                            <th scope="col">Usuario ID</th>
                            <th scope="col">Aceptado</th>
                            <th scope="col">Fecha Aceptación</th>
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
                            <td><?php echo $fila['turnoID']; ?></td>
                            <td><?php echo $fila['id_persona']; ?></td>
                            <td><?php echo $fila['id_usuario']; ?></td>
                            <td><?php echo $fila['aceptado'] ? 'Sí' : 'No'; ?></td>
                            <td><?php echo $fila['fecha_aceptacion']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['suplenciaID']; ?>" method="post" action="suplencia_modificar.php" style="display:inline;">
                                    <input type="hidden" name="suplenciaID" value="<?php echo $fila['suplenciaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['suplenciaID']; ?>" method="post" action="suplencia_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="suplenciaID" value="<?php echo $fila['suplenciaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('Desea realmente eliminar esta suplencia?');">Eliminar</button>
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
