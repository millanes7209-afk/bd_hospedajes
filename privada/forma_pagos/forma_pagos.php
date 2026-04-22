<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener las formas de pago
$sql = $db->Prepare("SELECT formaPagoID, empresaID, tipo, descripcion, _estado
                     FROM formas_pago
                     WHERE _estado <> 'X' 
                     ORDER BY formaPagoID DESC");

$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Formas de Pago</title>
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
            <h3>GESTIÓN DE FORMAS DE PAGO</h3>
        </div>
       
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="forma_pago_nueva.php" class="btn btn-success" role="button">Añadir Forma de Pago</a>
            </div>
            <p></p>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Descripción</th>
                            <th scope="col">Empresa ID</th>
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
                            <td><?php echo $fila['descripcion']; ?></td>
                            <td><?php echo $fila['empresaID']; ?></td>
                            <td>
                                <form name="formModif<?php echo $fila['formaPagoID']; ?>" method="post" action="forma_pago_modificar.php" style="display:inline;">
                                    <input type="hidden" name="formaPagoID" value="<?php echo $fila['formaPagoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <form name="formElimi<?php echo $fila['formaPagoID']; ?>" method="post" action="forma_pago_eliminar.php" style="display:inline;">
                                    <input type="hidden" name="formaPagoID" value="<?php echo $fila['formaPagoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger btn-accion" onclick="return confirm('¿Desea realmente eliminar esta forma de pago?');">Eliminar</button>
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
