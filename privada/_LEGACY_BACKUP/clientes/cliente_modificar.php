<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Obtener el ID del cliente que se va a modificar
$clienteID = $_POST["clienteID"];

// Consulta SQL para obtener los datos del cliente
$sql = $db->Prepare("
    SELECT *
    FROM clientes
    WHERE clienteID = ?
    AND _estado <> 'X'
");
$rs = $db->GetAll($sql, array($clienteID));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Cliente</title>
    <style>
        .form-control {
            border-color: black;
        }
        .card-body {
            padding: 25px;
        }
        .card {
            margin: 20px;
        }
        .invalid-feedback {
            color: red;
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
                            <h3>MODIFICAR CLIENTE</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($rs as $fila) { ?>
                            <form class="needs-validation" novalidate action="cliente_modificar1.php" method="post" name="formu">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="ci" class="form-label">(*) C.I.</label>
                                        <input type="text" class="form-control" name="ci" id="ci" required
                                            value="<?= htmlspecialchars($fila['ci']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                        <div class="invalid-feedback">
                                            Ingrese el C.I. del cliente.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nombres" class="form-label">(*) Nombres</label>
                                        <input type="text" class="form-control" name="nombres" id="nombres" required
                                            value="<?= htmlspecialchars($fila['nombres']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                        <div class="invalid-feedback">
                                            Ingrese los nombres del cliente.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellidos" class="form-label">(*) Apellidos</label>
                                        <input type="text" class="form-control" name="apellidos" id="apellidos" required
                                            value="<?= htmlspecialchars($fila['apellidos']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                        <div class="invalid-feedback">
                                            Ingrese los apellidos del cliente.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="lugar_nacimiento" class="form-label">(*) Lugar de Nacimiento</label>
                                        <input type="text" class="form-control" name="lugar_nacimiento" id="lugar_nacimiento" required
                                            value="<?= htmlspecialchars($fila['lugar_nacimiento']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                        <div class="invalid-feedback">
                                            Ingrese el lugar de nacimiento.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_nacimiento" class="form-label">(*) Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" name="fecha_nacimiento" id="fecha_nacimiento" required
                                            value="<?= htmlspecialchars($fila['fecha_nacimiento']) ?>">
                                        <div class="invalid-feedback">
                                            Ingrese la fecha de nacimiento.
                                        </div>
                                    </div>    
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="est_civil" class="form-label">Estado Civil</label>
                                        <input type="text" class="form-control" name="est_civil" id="est_civil"
                                            value="<?= htmlspecialchars($fila['est_civil']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="profesion" class="form-label">Profesión</label>
                                        <input type="text" class="form-control" name="profesion" id="profesion"
                                            value="<?= htmlspecialchars($fila['profesion']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                    </div>
                                </div>
                                

                                

                                <div class="row mb-3">
                                    <input type="hidden" name="clienteID" value="<?= htmlspecialchars($fila['clienteID']) ?>">
                                    <div class="col-md-12 text-center">
                                        <button class="btn btn-primary" type="submit">MODIFICAR</button>
                                        <button class="btn btn-secondary" type="button" onclick="window.history.back()">ATRÁS</button>
                                        <br>
                                        <small>(*) Datos Obligatorios</small>
                                    </div>
                                </div>
                            </form>
                            <?php } ?>
                        </div>
                    </div>
    </div>
    <script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
