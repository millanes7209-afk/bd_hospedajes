<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

$sql = $db->Prepare("SELECT * FROM roles WHERE _estado <> 'X' and id_rol>1 ORDER BY id_rol ASC");
$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Roles</title>
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
        .form-control {
            border-color: black;
        }
        .formita {
            padding: 25px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN DE ROLES</h3>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="rol_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Rol</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Rol</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs) : ?>
                            <?php $b = 1; ?>
                            <?php foreach ($rs as $fila) : ?>
                                <tr>
                                    <td><?php echo $b; ?></td>
                                    <td><?php echo $fila['rol']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila["id_rol"]; ?>" method="post" action="rol_modificar.php" style="display:inline;">
                                            <input type="hidden" name="id_rol" value="<?php echo $fila['id_rol']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form name="formElimi<?php echo $fila["id_rol"]; ?>" method="post" action="rol_eliminar.php" style="display:inline;">
                                            <input type="hidden" name="id_rol" value="<?php echo $fila['id_rol']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea realmente eliminar el rol <?php echo $fila["rol"]; ?>?');">Eliminar</button>
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
