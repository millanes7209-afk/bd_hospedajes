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
    <title>Formulario de Inserción de Cliente</title>
    <style>
        .form-control{
            border-color: black;
        }
        .card-body {
            padding: 25px; 
        }
    </style>
</head>
<body>

<div class="container my-5">
<?php
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $mensaje_tipo = $_SESSION['mensaje_tipo'];

    echo "<div class='alert alert-$mensaje_tipo alert-dismissible fade show' role='alert'>";
    echo $mensaje;
    echo "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>";
    echo "</div>";

    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_tipo']);
}
?>

    <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="form-container">
              <div class="card">
                  <div class="card-header">
                    <h3>AGREGAR CLIENTE</h3>
                  </div>
                  <div class="card-body">
                      <form class="needs-validation" novalidate action="cliente_nuevo1.php" method="post" name="formu">
                          <div class=" row mb-3">
                            <div class="col-md-6">
                                    <label for="ci" class="form-label">(*) C.I.</label>
                                    <input type="text" class="form-control" name="ci" id="ci" size="10" required>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                            </div>
                            <div class="col-md-6">
                                    <label for="nombres" class="form-label">(*) Nombres</label>
                                    <input type="text" class="form-control" name="nombres" id="nombres" size="10" required
                                    pattern="^[A-Za-zÀ-ÿ\s]+$" onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Ingrese solo texto.
                                    </div>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                                    <label for="apellidos" class="form-label">(*) Apellidos</label>
                                    <input type="text" class="form-control" name="apellidos" id="apellidos" size="10" required
                                    pattern="^[A-Za-zÀ-ÿ\s]+$" onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                    Ingrese solo texto.
                                    </div>
                            </div>
                            <div class="col-md-6">
                                    <label for="fecha_nacimiento" class="form-label">(*) Fecha Nacimiento</label>
                                    <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" size="10" required>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                            </div>
                          </div>    
                          <div class="row mb-3">
                            <div class="col-md-6">
                                    <label for="lugar_nacimiento" class="form-label">(*) Lugar Nacimiento</label>
                                    <input type="text" class="form-control" name="lugar_nacimiento" id="lugar_nacimiento" size="10" required
                                     onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                            </div>
                          </div>
                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="est_civil" class="form-label">(*) Estado Civil</label>
                                <input type="text" class="form-control" name="est_civil" id="est_civil" size="10" 
                                pattern="[A-Za-z\s]+" onkeyup="this.value=this.value.toUpperCase()">
                            </div>
                            <div class="col-md-6">
                                <label for="profesion" class="form-label">(*) Profesión</label>
                                <input type="text" class="form-control" name="profesion" id="profesion" size="10" 
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
