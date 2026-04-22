<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

$empresaID =1;

$sql = $db->Prepare("SELECT *
                     FROM empresa
                     WHERE empresaID = ?
                     AND _estado <> 'X'                        
                        ");
$rs = $db->GetAll($sql, array($empresaID));
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Modificación Empresa</title>
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="form-container">
                    <div class="card">
                        <div class="card-header">
                        <h3>MODIFICAR EMPRESA</h3>
                        </div>
                    <div class="card-body">
                    <?php foreach ($rs as $k => $fila) { ?>
                    <form class="needs-validation" novalidate action="dulces_modificar1.php" method="post" name="formu">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">(*) C.I.</label>
                                <input type="text" class="form-control" name="nombre" id="nombre" size="20" required
                                    value="<?= htmlspecialchars($rs[0]['nombre']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                    Ingrese su cedula de identidad
                                </div>
                            </div>
                            <div class="col-md-6">
                                  <label for="direccion" class="form-label">direccion</label>
                                  <input type="text" class="form-control" name="direccion" id="direccion" size="20" required
                                    value="<?= htmlspecialchars($rs[0]['direccion']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                  Ingrese solo texto
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telefono" class="form-label">telefono</label>
                                <input type="text" class="form-control" name="telefono" id="telefono" size="20" required
                                    value="<?= htmlspecialchars($rs[0]['telefono']) ?>" onkeyup="this.value=this.value.toUpperCase()">
                                <div class="invalid-feedback">
                                  Ingrese solo texto
                                </div>
                            </div>
                            <div class="col-md-6">
                                    <label for="logo_agencia" class="form-label">Logo</label>
                                    <input type="file" class="form-control" id="logo_agencia" name="logo_agencia">
                                    <input type="hidden" name="logo_hidden" value="<?= htmlspecialchars($fila['logo_agencia']) ?>">
                                </div>
                        </div>

                        <div class="row mb-3">
                        <input type="hidden" name="empresaID" value="<?= htmlspecialchars($fila['empresaID']) ?>">
                            <div class="col-md-12 text-center">
                                <button class="btn btn-primary" type="submit" onclick="validar()">MODIFICAR</button>
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
