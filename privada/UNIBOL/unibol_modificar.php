<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = 1;

// Consulta para obtener la información de la empresa
$sql = $db->Prepare("SELECT   *
                     FROM     emp_mensajeria
                     WHERE    empresaID = ?
                     AND      _estado <> 'X'                        
                    ");
$rs = $db->GetAll($sql, array($empresaID));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Datos de Empresa</title>
    <script type="text/javascript" src="../js/validacion_obligatorios.js"></script>
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
                            <h3>MODIFICAR DATOS</h3>
                        </div>
                        <div class="card-body">
                            <?php foreach ($rs as $fila) { ?>
                            <form action="unibol_modificar1.php" method="post" name="formu" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="row mb-3">
                                  <div class="col-md-6">
                                      <label for="nombre" class="form-label">(*) Empresa</label>
                                      <input type="text" class="form-control" id="nombre" name="nombre" 
                                      onkeyup="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($fila['nombre']) ?>" required>
                                    <div class="invalid-feedback">
                                        Por favor, ingrese el nombre de la empresa.
                                    </div>
                                  </div>
                                  <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono" 
                                        onkeyup="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($fila['telefono']) ?>" required>
                                      <div class="invalid-feedback">
                                        Ingrese el telefono de la empresa.
                                      </div>
                                  </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="direccion" class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="direccion" name="direccion" onkeyup="this.value=this.value.toUpperCase()" value="<?= htmlspecialchars($fila['direccion']) ?>">
                                  </div>
                                <div class="mb-3">
                                    <label for="logo_agencia" class="form-label">Logo</label>
                                    <input type="file" class="form-control" id="logo_agencia" name="logo_agencia">
                                    <input type="hidden" name="logo_hidden" value="<?= htmlspecialchars($fila['logo_agencia']) ?>">
                                </div>
                                <input type="hidden" name="empresaID" value="<?= htmlspecialchars($fila['empresaID']) ?>">
                                <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Modificar Acceso</button>
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
</body>
</html>
