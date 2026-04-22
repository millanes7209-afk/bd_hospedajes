<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$cargoID = $_POST["cargoID"];

// Consulta para obtener la información del cargo
$sql = $db->Prepare("SELECT *
                     FROM cargos
                     WHERE cargoID = ?
                     AND _estado <> 'X'");
$rs = $db->GetAll($sql, array($cargoID));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Cargo</title>
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
                        <h3>MODIFICAR CARGO</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="cargo_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="cargo" class="form-label">(*) Cargo</label>
                                    <input type="text" class="form-control" name="cargo" id="cargo" value="<?= htmlspecialchars($fila['cargo']) ?>" required onkeyup="this.value=this.value.toUpperCase()">
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label">(*) Descripción</label>
                                    <textarea class="form-control" name="descripcion" id="descripcion" rows="3" required onkeyup="this.value=this.value.toUpperCase()"><?= htmlspecialchars($fila['descripcion']) ?></textarea>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="cargoID" value="<?= htmlspecialchars($fila['cargoID']) ?>">

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Modificar Cargo</button>
                                    <button class="btn btn-secondary" type="reset">Borrar</button>
                                    <br>
                                    <small>(*) Datos Obligatorios</small>
                                </div>
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
