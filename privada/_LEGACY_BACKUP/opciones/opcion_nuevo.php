<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consultas para obtener grupos desde la base de datos
$grupos = $db->GetAll("SELECT id_grupo, grupo FROM grupos WHERE _estado <> 'X'");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Opción</title>
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
                        <h3>AGREGAR OPCIÓN</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="opcion_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_grupo" class="form-label">(*) Grupo</label>
                                    <select class="form-control" name="id_grupo" id="id_grupo" required>
                                        <option value="">Seleccione un grupo</option>
                                        <?php
                                        foreach ($grupos as $grupo) {
                                            echo "<option value='" . htmlspecialchars($grupo['id_grupo']) . "'>" . htmlspecialchars($grupo['grupo']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un grupo.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="opcion" class="form-label">(*) Opción</label>
                                    <input type="text" class="form-control" name="opcion" id="opcion" size="20" required onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="contenido" class="form-label">(*) Contenido</label>
                                    <input type="text" class="form-control" name="contenido" id="contenido" size="20" required>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="orden" class="form-label">(*) Orden</label>
                                    <input type="number" class="form-control" name="orden" id="orden" size="20" required>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
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
