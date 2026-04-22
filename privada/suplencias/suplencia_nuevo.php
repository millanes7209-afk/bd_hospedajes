<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Suplencia</title>
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
    </style>
</head>
<body>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="card">
                        <div class="card-header">
                            <h3>AGREGAR SUPLENCIA</h3>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate action="suplencia_nuevo_procesar.php" method="post" name="formu">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="turnoID" class="form-label">(*) Turno</label>
                                        <select class="form-control" name="turnoID" id="turnoID" required>
                                            <option value="">Seleccione un turno</option>
                                            <?php
                                            // Cargar opciones de turnos desde la base de datos
                                            $sql_turnos = $db->Prepare("SELECT turnoID, tipo FROM turnos WHERE _estado <> 'X'");
                                            $rs_turnos = $db->GetAll($sql_turnos);
                                            foreach ($rs_turnos as $turno) {
                                                echo "<option value='{$turno['turnoID']}'>{$turno['tipo']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_persona" class="form-label">(*) Persona</label>
                                        <select class="form-control" name="id_persona" id="id_persona" required>
                                            <option value="">Seleccione una persona</option>
                                            <?php
                                            // Cargar opciones de personas desde la base de datos
                                            $sql_personas = $db->Prepare("SELECT id_persona, CONCAT(ap, ' ', am, ' ', nombres) AS nombre_completo FROM personas");
                                            $rs_personas = $db->GetAll($sql_personas);
                                            foreach ($rs_personas as $persona) {
                                                echo "<option value='{$persona['id_persona']}'>{$persona['nombre_completo']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="id_usuario" class="form-label">(*) Usuario</label>
                                        <select class="form-control" name="id_usuario" id="id_usuario" required>
                                            <option value="">Seleccione un usuario</option>
                                            <?php
                                            // Cargar opciones de usuarios desde la base de datos
                                            $sql_usuarios = $db->Prepare("SELECT id_usuario, nombre_usuario FROM usuarios");
                                            $rs_usuarios = $db->GetAll($sql_usuarios);
                                            foreach ($rs_usuarios as $usuario) {
                                                echo "<option value='{$usuario['id_usuario']}'>{$usuario['nombre_usuario']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_aceptacion" class="form-label">Fecha de Aceptación</label>
                                        <input type="datetime-local" class="form-control" name="fecha_aceptacion" id="fecha_aceptacion">
                                    </div>
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
