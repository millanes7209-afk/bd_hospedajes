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
    <title>Formulario de Inserción de Asignación</title>
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
        .form-check {
            margin-right: 10px;
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
                            <h3>AGREGAR ASIGNACIÓN</h3>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate action="asignacion_nuevo1.php" method="post" name="formu">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="id_persona" class="form-label">(*) Empleado</label>
                                        <select class="form-control" name="id_persona" id="id_persona" required>
                                            <option value="">Seleccione un empleado</option>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="fecha_inicio" class="form-label">(*) Fecha Inicio</label>
                                        <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio" required>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_fin" class="form-label">(*) Fecha Fin</label>
                                        <input type="date" class="form-control" name="fecha_fin" id="fecha_fin" required>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label">(*) Días</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="lunes" value="LUNES">
                                            <label class="form-check-label" for="lunes">Lunes</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="martes" value="MARTES">
                                            <label class="form-check-label" for="martes">Martes</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="miercoles" value="MIÉRCOLES">
                                            <label class="form-check-label" for="miercoles">Miércoles</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="jueves" value="JUEVES">
                                            <label class="form-check-label" for="jueves">Jueves</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="viernes" value="VIERNES">
                                            <label class="form-check-label" for="viernes">Viernes</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="sabado" value="SÁBADO">
                                            <label class="form-check-label" for="sabado">Sábado</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="dias[]" id="domingo" value="DOMINGO">
                                            <label class="form-check-label" for="domingo">Domingo</label>
                                        </div>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
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
