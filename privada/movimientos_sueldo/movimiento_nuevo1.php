<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// Consulta para obtener los datos de los movimientos de sueldo
$sql = $db->Prepare("SELECT movimientoID, propietarioID, id_persona, tipo_movimiento, monto, fecha_hora, estado, _estado
                     FROM movimientos_sueldo
                     WHERE _estado <> 'X' 
                     ORDER BY movimientoID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Movimientos de Sueldo</title>
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
            <h3>GESTIÓN DE MOVIMIENTOS DE SUELDO</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="movimiento_nuevo.php" class="btn btn-success" role="button">Añadir Movimiento</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Propietario ID</th>
                            <th scope="col">Persona ID</th>
                            <th scope="col">Tipo Movimiento</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Fecha y Hora</th>
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
                            <td><?php echo $b; ?></td>
                            <td><?php echo $fila['propietarioID']; ?></td>
                            <td><?php echo $fila['id_persona']; ?></td>
                            <td><?php echo $fila['tipo_movimiento']; ?></td>
                            <td><?php echo $fila['monto']; ?></td>
                            <td><?php echo $fila['fecha_hora']; ?></td>
                            <td><?php echo $fila['estado']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['movimientoID']; ?>" method="post" action="movimiento_modificar.php" style="display:inline;">
                                    <input type="hidden" name="movimientoID" value="<?php echo $fila['movimientoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['movimientoID']; ?>" method="post" action="movimiento_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="movimientoID" value="<?php echo $fila['movimientoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea realmente eliminar el movimiento?');">Eliminar</button>
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
