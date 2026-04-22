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
    <title>Formulario de Inserción de Empleado</title>
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
                        <h3>AGREGAR EMPLEADO</h3>
                        </div>
                    <div class="card-body">
                    <form class="needs-validation" novalidate action="Empleado_nuevo1.php" method="post" name="formu">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="ci" class="form-label">(*) C.I.</label>
                                <input type="text" class="form-control" name="ci" id="ci" size="10" required>
                                <div class="invalid-feedback">
                                    Este campo es obligatorio.
                                </div>
                            </div>
                            <div class="col-md-6">
                                  <label for="paterno" class="form-label">Apellido Paterno</label>
                                  <input type="text" class="form-control" name="ap" id="paterno" size="20" 
                                  pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" onkeyup="this.value=this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                  Ingrese solo texto.
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="materno" class="form-label">Apellido Materno</label>
                                <input type="text" class="form-control" name="am" id="materno" size="20" 
                                pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" onkeyup="this.value=this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                  Ingrese solo texto.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="nombres" class="form-label">(*) Nombres</label>
                                <input type="text" class="form-control" name="nombres" id="nombres" size="20" 
                                pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" required onkeyup="this.value=this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                    Ingrese solo texto.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                        <div class="col-md-6">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" name="telefono" id="telefono" size="10">
                            </div>
                            <div class="col-md-6">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" name="direccion" id="direccion" size="20" onkeyup="this.value=this.value.toUpperCase()">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                    <label for="cargoID" class="form-label">(*) Cargo</label>
                                    <select class="form-control" name="cargoID" id="cargoID" required>
                                        <option value="">Seleccione un cargo</option>
                                        <?php
                                        // Cargar opciones de EMPLEADOS desde la base de datos
                                        $sql_cargos = $db->Prepare("SELECT cargoID,cargo FROM cargos");
                                        $rs_cargos = $db->GetAll($sql_cargos);
                                        foreach ($rs_cargos as $Empleado) {
                                            echo "<option value='{$Empleado['cargoID']}'>{$Empleado['cargo']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                            </div>
                            <div class="col-md-6">
                                <label for="sueldo" class="form-label">Sueldo</label>
                                <input type="number" class="form-control" name="sueldo" required
                                min="1" step="1" id="sueldo" size="20">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="fecha_contratacion" class="form-label">Fecha contratación</label>
                                <input type="date" class="form-control" name="fecha_contratacion" 
                                required id="fecha_contratacion" size="20">
                            </div>
                        
                            <!--
                            <div class="col-md-6">
                                <label class="form-label">(*) Género</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="femenino" value="F" required>
                                    <label class="form-check-label" for="femenino">Femenino</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="genero" id="masculino" value="M" required>
                                    <label class="form-check-label" for="masculino">Masculino</label>
                                </div>
                            </div>
                                    -->
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
