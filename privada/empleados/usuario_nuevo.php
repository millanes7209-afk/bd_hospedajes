<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// $db->debug=true;

$empresaID = $_SESSION['empresaID'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Usuario</title>
    <style>
        .card {
            margin: 20px;
        }
        .form-control {
            border-color: black;
        }
        .formita {
            padding: 25px;
        }
        thead {
            color: black;
            background: #b5b5b5;
        }
        tr {
            color: black;
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>AGREGAR USUARIO</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="usuario_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                        <label for="empleadoID" class="form-label">(*) Empleado</label>
                                        <select class="form-control" name="empleadoID" id="empleadoID" required>
                                            <option value="">Seleccione un empleado</option>
                                            <?php
                                            // Cargar opciones de empleados de la empresa actual
                                            $sql_empleados = "SELECT e.empleadoID, CONCAT_WS(' ', e.nombres, e.apellidos) as empleado 
                                                              FROM empleados e
                                                              INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
                                                              WHERE e._estado='A' AND ee.empresaID = ? AND ee._estado <> 'X' AND e.empleadoID > 1";
                                            $rs_empleados = $db->obtenerTodo($sql_empleados, [$empresaID]);
                                            foreach ($rs_empleados as $empleado) {
                                                echo "<option value='{$empleado['empleadoID']}'>{$empleado['empleado']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="usuario" class="form-label"><b>(*) Nombre de usuario</b></label>
                                    <input type="text" class="form-control" name="usuario" id="usuario" required>
                                    <div class="invalid-feedback">
                                        Usuario obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="clave" class="form-label"><b>(*) Clave</b></label>
                                    <input type="password" class="form-control" name="clave" id="clave" required>
                                    <div class="invalid-feedback">
                                        Clave obligatoria.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Aceptar</button>
                                    <button class="btn btn-secondary" type="button" onclick="history.back()">Atrás</button>
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
