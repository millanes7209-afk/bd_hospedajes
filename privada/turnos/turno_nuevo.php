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
    <title>Formulario de Inserción de Turno</title>
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
                        <h3>AGREGAR TURNO</h3>
                        </div>
                    <div class="card-body">
                    <form class="needs-validation" novalidate action="turno_nuevo1.php" method="post" name="formu">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tipo" class="form-label">(*) Tipo de Turno</label>
                                <input type="text" class="form-control" name="tipo" id="tipo" size="20" required
                                onkeyup="this.value=this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                    Este campo es obligatorio.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="hora_inicio" class="form-label">(*) Hora de Inicio</label>
                                <input type="time" class="form-control" name="hora_inicio" id="hora_inicio" required>
                                <div class="invalid-feedback">
                                    Este campo es obligatorio.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="hora_fin" class="form-label">(*) Hora de Fin</label>
                                <input type="time" class="form-control" name="hora_fin" id="hora_fin" required>
                                <div class="invalid-feedback">
                                    Este campo es obligatorio.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" name="descripcion" id="descripcion" size="200"
                                onkeyup="this.value=this.value.toUpperCase()">
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
