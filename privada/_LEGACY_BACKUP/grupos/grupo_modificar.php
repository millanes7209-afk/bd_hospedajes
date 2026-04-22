<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
// $db->debug=true;

$id_grupo = $_POST["id_grupo"];

$sql = $db->Prepare("SELECT *
                     FROM grupos
                     WHERE id_grupo = ?
                     AND _estado <> 'X'");
$rs = $db->GetAll($sql, array($id_grupo));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Modificación de Grupo</title>
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
                        <h3>MODIFICAR GRUPO</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $k => $fila) { ?>
                        <form class="needs-validation" novalidate action="grupo_modificar1.php" method="post" name="formu">
                            <div class="mb-3">
                                <label for="grupo" class="form-label">(*) Grupo</label>
                                <input type="text" class="form-control" name="grupo" id="grupo" size="30"
                                       value="<?= htmlspecialchars($fila['grupo']) ?>" 
                                       onkeyup="this.value=this.value.toUpperCase()" required>
                                <div class="invalid-feedback">
                                    Ingrese el nombre del grupo
                                </div>
                            </div>
                            <input type="hidden" name="id_grupo" value="<?= htmlspecialchars($fila['id_grupo']) ?>">
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
