<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener las opciones
$opciones = $db->GetAll("SELECT id_opcion, opcion
                          FROM opciones
                          WHERE _estado = 'A'");

// Consulta para obtener los roles
$roles = $db->GetAll("SELECT id_rol, rol
                      FROM roles
                      WHERE _estado = 'A'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Accesos</title>
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
                        <h3>AGREGAR ACCESO</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="acceso_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_opcion" class="form-label">(*) Opción</label>
                                    <select class="form-control" name="id_opcion" id="id_opcion" required>
                                        <option value="">--Seleccione--</option>
                                        <?php
                                        foreach ($opciones as $opcion) {
                                            echo "<option value='" . htmlspecialchars($opcion['id_opcion']) . "'>" . htmlspecialchars($opcion['opcion']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione una opción.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">(*) Rol</label>
                                    <select class="form-control" name="id_rol" id="id_rol" required>
                                        <option value="">--Seleccione--</option>
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
