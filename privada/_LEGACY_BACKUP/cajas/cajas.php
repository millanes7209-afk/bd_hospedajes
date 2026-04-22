<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// Consulta para obtener los datos de las cajas
$sql = $db->Prepare("SELECT cajaID, empresaID, fecha_apertura, fecha_cierre, estado, turnoID, _estado
                     FROM cajas
                     WHERE _estado <> 'X' 
                     ORDER BY cajaID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Cajas</title>
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
            <h3>GESTIÓN DE CAJAS</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="caja_nueva.php" class="btn btn-success" role="button">Añadir Caja</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Empresa ID</th>
                            <th scope="col">Fecha Apertura</th>
                            <th scope="col">Fecha Cierre</th>
                            <th scope="col">Estado Caja</th>
                            <th scope="col">Turno ID</th>
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
                            <td><?php echo $fila['empresaID']; ?></td>
                            <td><?php echo $fila['fecha_apertura']; ?></td>
                            <td><?php echo $fila['fecha_cierre']; ?></td>
                            <td><?php echo $fila['estado']; ?></td>
                            <td><?php echo $fila['turnoID']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['cajaID']; ?>" method="post" action="caja_modificar.php" style="display:inline;">
                                    <input type="hidden" name="cajaID" value="<?php echo $fila['cajaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['cajaID']; ?>" method="post" action="caja_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="cajaID" value="<?php echo $fila['cajaID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('Desea realmente eliminar la caja?');">Eliminar</button>
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
