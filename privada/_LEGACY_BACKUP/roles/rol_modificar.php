<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// $db->debug=true;

$id_rol = $_POST["id_rol"];

$sql = $db->Prepare("SELECT *
                     FROM roles
                     WHERE id_rol = ?
                     AND _estado <> 'X'");
$rs = $db->GetAll($sql, array($id_rol));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Modificación de Rol</title>
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
                        <h3>MODIFICAR ROL</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $k => $fila) { ?>
                        <form class="needs-validation" novalidate action="rol_modificar1.php" method="post" name="formu">
                            <div class="mb-3">
                                <label for="rol" class="form-label">(*) Rol</label>
                                <input type="text" class="form-control" name="rol" id="rol" size="30"
                                       value="<?= htmlspecialchars($fila['rol']) ?>" 
                                       onkeyup="this.value=this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    Ingrese el nombre del rol
                                </div>
                            </div>
                            <input type="hidden" name="id_rol" value="<?= htmlspecialchars($fila['id_rol']) ?>">
                            <div class="text-center">
                                <button class="btn btn-primary" type="submit" onclick="validar()">MODIFICAR</button>
                                <button class="btn btn-secondary" type="reset">Borrar</button>
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
