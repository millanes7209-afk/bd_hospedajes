<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$id_usuario_rol = $_POST["id_usuario_rol"];


$sql = $db->Prepare("SELECT *
                     FROM   usuarios_roles
                     WHERE  id_usuario_rol = ?
                     AND    _estado <> 'X'
                     ");
$rs = $db->GetAll($sql, array($id_usuario_rol));

// Consultas para obtener listas de usuarios y roles
$usuarios = $db->GetAll(" SELECT id_usuario, usuario 
                          FROM usuarios 
                          WHERE _estado <> 'X'
                          ");

$roles = $db->GetAll("    SELECT id_rol, rol 
                          FROM roles 
                          WHERE _estado <> 'X'
                          ");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Modificación de Relación Usuario-Rol</title>
    <style>
        .form-control {
            border-color: black;
        }
        .card-body {
            padding: 25px; 
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>MODIFICAR RELACIÓN USUARIO-ROL</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="usuario_rol_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_usuario" class="form-label">(*) Usuario</label>
                                    <select class="form-control" name="id_usuario" id="id_usuario" required>
                                        <?php
                                        foreach ($usuarios as $usuario) {
                                            $selected = ($usuario['id_usuario'] == $fila['id_usuario']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($usuario['id_usuario']) . "' $selected>" . htmlspecialchars($usuario['usuario']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un usuario.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">(*) Rol</label>
                                    <select class="form-control" name="id_rol" id="id_rol" required>
                                        <?php
                                        foreach ($roles as $rol) {
                                            $selected = ($rol['id_rol'] == $fila['id_rol']) ? 'selected' : '';
                                            echo "<option value='" . htmlspecialchars($rol['id_rol']) . "' $selected>" . htmlspecialchars($rol['rol']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un rol.
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id_usuario_rol" value="<?= htmlspecialchars($fila['id_usuario_rol']) ?>">

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Modificar</button>
                                    <button class="btn btn-secondary" type="reset">Borrar</button>
                                    <br>
                                    <small>(*) Datos Obligatorios</small>
                                </div>
                            </div>
                        </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
