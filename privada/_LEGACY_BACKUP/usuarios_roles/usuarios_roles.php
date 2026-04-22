<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

// Consulta para obtener los datos de los usuarios y roles
$sql = $db->Prepare("SELECT ur.id_usuario_rol, u.usuario, r.rol
                     FROM usuarios_roles ur
                     JOIN usuarios u ON ur.id_usuario = u.id_usuario
                     JOIN roles r ON ur.id_rol = r.id_rol
                     WHERE ur._estado <> 'X'
                     ORDER BY ur.id_usuario_rol ASC");
$rs = $db->GetAll($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Usuarios y Roles</title>
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
            <h3>GESTIÓN DE USUARIOS Y ROLES</h3>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="usuario_rol_nuevo.php" class="btn btn-success mb-3" role="button">Añadir Usuario-Rol</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Usuario</th>
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
                                    <td><?php echo $fila['usuario']; ?></td>
                                    <td><?php echo $fila['rol']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila['id_usuario_rol']; ?>" method="post" action="usuario_rol_modificar.php" style="display:inline;">
                                            <input type="hidden" name="id_usuario_rol" value="<?php echo $fila['id_usuario_rol']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form name="formElimi<?php echo $fila['id_usuario_rol']; ?>" method="post" action="usuario_rol_eliminar.php" style="display:inline;">
                                            <input type="hidden" name="id_usuario_rol" value="<?php echo $fila['id_usuario_rol']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Desea realmente eliminar la relación entre el usuario <?php echo $fila['usuario']; ?> y el rol <?php echo $fila['rol']; ?>?');">Eliminar</button>
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
