<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// Consulta para obtener los datos de los turnos
$sql = $db->Prepare("SELECT turnoID, empresaID, tipo, hora_inicio, hora_fin, descripcion, _estado
                     FROM turnos
                     WHERE _estado <> 'X' 
                     ORDER BY turnoID ASC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Turnos</title>
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
            <h3>GESTIÓN TURNOS</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="turno_nuevo.php" class="btn btn-success" role="button">Añadir Turno</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Hora Inicio</th>
                            <th scope="col">Hora Fin</th>
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
                            <td><?php echo $fila['tipo']; ?></td>
                            <td><?php echo date("h:i A", strtotime($fila['hora_inicio'])); ?></td>
                            <td><?php echo date("h:i A", strtotime($fila['hora_fin'])); ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['turnoID']; ?>" method="post" action="turno_modificar.php" style="display:inline;">
                                    <input type="hidden" name="turnoID" value="<?php echo $fila['turnoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['turnoID']; ?>" method="post" action="turno_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="turnoID" value="<?php echo $fila['turnoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('Desea realmente eliminar el turno?');">Eliminar</button>
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
