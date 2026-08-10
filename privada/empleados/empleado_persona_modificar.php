<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php"); // Si es necesario, descomenta esta línea
// $db->debug=true;

$empleadoID = $_POST["empleadoID"];
$empresaID = $_SESSION['empresaID'];
// Consulta para obtener los datos del empleado
$sql = $db->Prepare("
    SELECT *
    FROM empleados
    WHERE empleadoID = ?
    AND _estado <> 'X'
");
$rs = $db->GetAll($sql, array($empleadoID));
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modificar Empleado</title>
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
                        <h3>MODIFICAR EMPLEADO</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="Empleado_modificar1.php" method="post" name="formu">
                            <!-- Campos ocultos -->
                            <input type="hidden" name="empleadoID" value="<?= htmlspecialchars($fila['empleadoID']) ?>">
                            <div class="row mb-3">
                                <!-- C.I. -->
                                <div class="col-md-6">
                                    <label for="ci" class="form-label">(*) C.I.</label>
                                    <input type="text" class="form-control" name="ci" id="ci" required
                                        value="<?= htmlspecialchars($fila['ci']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Ingrese su cédula de identidad.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="apellidos" class="form-label">(*) Apellidos</label>
                                    <input type="text" class="form-control" name="apellidos" id="apellidos" required
                                        value="<?= htmlspecialchars($fila['apellidos']) ?>" onkeyup="this.value=this.value.toUpperCase()"
                                        pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">
                                        Ingrese solo letras.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <!-- Nombres -->
                                <div class="col-md-6">
                                    <label for="nombres" class="form-label">(*) Nombres</label>
                                    <input type="text" class="form-control" name="nombres" id="nombres" required
                                        value="<?= htmlspecialchars($fila['nombres']) ?>" onkeyup="this.value=this.value.toUpperCase()"
                                        pattern="[A-Za-z\s]+">
                                    <div class="invalid-feedback">
                                        Ingrese solo letras.
                                    </div>
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="telefono" id="telefono" required
                                        value="<?= htmlspecialchars($fila['telefono']) ?>">
                                </div>
                            </div>    
                            <div class="row mb-3">
                                <!-- Género -->
                                <div class="col-md-6">
                                    <label class="form-label">(*) Género</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="genero" id="femenino" value="F"
                                            <?= htmlspecialchars($fila['genero']) == 'F' ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="femenino">Femenino</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="genero" id="masculino" value="M"
                                            <?= htmlspecialchars($fila['genero']) == 'M' ? 'checked' : '' ?> required>
                                        <label class="form-check-label" for="masculino">Masculino</label>
                                    </div>
                                    <div class="invalid-feedback">
                                        Seleccione el género.
                                    </div>
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="col-md-6">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento"
                                        value="<?= htmlspecialchars($fila['fecha_nacimiento']) ?>">
                                </div>
                            </div>
                            


                            <!-- Botones -->
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit">Modificar</button>
                                <button class="btn btn-secondary" type="button" onclick="history.back()">Atrás</button>
                                <br>
                                <small>(*) Datos Obligatorios</small>
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
