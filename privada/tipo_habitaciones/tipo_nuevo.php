<?php
// Sección PHP: Lógica de servidor y consulta a la base de datos
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción Tipo Habitación</title>
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
                        <h3>AGREGAR TIPO HABITACIÓN</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="tipo_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tipo" class="form-label">(*) Tipo</label>
                                    <input type="text" class="form-control" name="tipo" id="tipo"required 
                                    onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Número de habitación necesario.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="precio" class="form-label">(*) Precio</label>
                                    <input type="number" class="form-control" name="precio" id="precio"required 
                                    min="1" step="1">
                                    <div class="invalid-feedback">
                                        Introduzca un precio mayor a cero.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label">(*) Descripción</label>
                                    <textarea class="form-control" name="descripcion" id="descripcion"
                                    onkeyup="this.value=this.value.toUpperCase()"></textarea>
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
