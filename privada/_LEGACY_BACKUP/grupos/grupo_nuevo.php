<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// $db->debug=true;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Grupo</title>
    <style>
        .form-control{
            border-color: black;
        }
        .card-body {
            padding: 25px; 
        }
    </style>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="form-container">
              <div class="card">
                  <div class="card-header">
                    <h3>AGREGAR GRUPO</h3>
                  </div>
                  <div class="card-body">
                      <form class="needs-validation" novalidate action="grupo_nuevo1.php" method="post" name="formu">
                          <div class="mb-3">
                              <label for="grupo" class="form-label">(*) Grupo</label>
                              <input type="text" class="form-control" name="grupo" id="grupo" size="20" onkeyup="this.value=this.value.toUpperCase()" required>
                              <div class="invalid-feedback">
                                    Este campo es obligatorio.
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
