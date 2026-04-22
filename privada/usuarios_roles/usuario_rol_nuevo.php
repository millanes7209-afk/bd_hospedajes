<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consultas para obtener usuarios y roles desde la base de datos
$usuarios = $db->GetAll("   SELECT  id_usuario, usuario 
                            FROM    usuarios 
                            WHERE   _estado <> 'X'
                            ");
$roles = $db->GetAll("      SELECT id_rol, rol 
                            FROM roles 
                            WHERE _estado <> 'X'
                            ");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Relación Usuario-Rol</title>
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
                        <h3>AGREGAR RELACIÓN USUARIO-ROL</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="usuario_rol_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_usuario" class="form-label">(*) Usuario</label>
                                    <select class="form-control" name="id_usuario" id="id_usuario" required>
                                        <!-- Llenado del select con los usuarios obtenidos de la base de datos -->
                                        <?php
                                        foreach ($usuarios as $usuario) {
                                            echo "<option value='" . htmlspecialchars($usuario['id_usuario']) . "'>" . htmlspecialchars($usuario['usuario']) . "</option>";
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
                                        <!-- Llenado del select con los roles obtenidos de la base de datos -->
                                        <?php
                                        foreach ($roles as $rol) {
                                            echo "<option value='" . htmlspecialchars($rol['id_rol']) . "'>" . htmlspecialchars($rol['rol']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un rol.
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Aceptar</button>
                                    <button class="btn btn-secondary" type="reset">Borrar</button>
                                    <br>
                                    <small>(*) Datos Obligatorios</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
