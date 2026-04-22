<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$id_rol = $_POST["id_rol"];
$id_opcion = $_POST["id_opcion"];
$id_acceso = $_POST["id_acceso"];

// Consulta para obtener la información del acceso
$sql = $db->Prepare("SELECT *
                     FROM   accesos
                     WHERE  id_acceso = ?
                     AND    _estado = 'A'
                     ");
$rs = $db->GetAll($sql, array($id_acceso));

// Consulta para obtener el rol asociado al acceso
$sql1 = $db->Prepare("SELECT  *
                     FROM     roles
                     WHERE    id_rol = ?
                     AND      _estado = 'A'
                     ");
$rs1 = $db->GetAll($sql1, array($id_rol));

// Consulta para obtener los roles que no están asociados al acceso
$sql2 = $db->Prepare("SELECT  *
                     FROM     roles
                     WHERE    id_rol <> ?
                     AND      _estado = 'A'
                     ");
$rs2 = $db->GetAll($sql2, array($id_rol));

// Consulta para obtener la opción asociada al acceso
$sql3 = $db->Prepare("SELECT  *
                     FROM     opciones
                     WHERE    id_opcion = ?
                     AND      _estado = 'A'
                     ");
$rs3 = $db->GetAll($sql3, array($id_opcion));

// Consulta para obtener las opciones que no están asociadas al acceso
$sql4 = $db->Prepare("SELECT  *
                     FROM     opciones
                     WHERE    id_opcion <> ?
                     AND      _estado = 'A'
                     ");
$rs4 = $db->GetAll($sql4, array($id_opcion));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Acceso</title>
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
                        <h3>MODIFICAR ACCESO</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="acceso_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_opcion" class="form-label">(*) Opción</label>
                                    <select class="form-control" name="id_opcion" id="id_opcion" required>
                                        <?php
                                        foreach ($rs3 as $opcion) {
                                            echo "<option value='" . htmlspecialchars($opcion['id_opcion']) . "'>" . htmlspecialchars($opcion['opcion']) . "</option>";
                                        }
                                        foreach ($rs4 as $opcion) {
                                            echo "<option value='" . htmlspecialchars($opcion['id_opcion']) . "'>" . htmlspecialchars($opcion['opcion']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione una opción.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="id_rol" class="form-label">(*) Rol</label>
                                    <select class="form-control" name="id_rol" id="id_rol" required>
                                        <?php
                                        foreach ($rs1 as $rol) {
                                            echo "<option value='" . htmlspecialchars($rol['id_rol']) . "'>" . htmlspecialchars($rol['rol']) . "</option>";
                                        }
                                        foreach ($rs2 as $rol) {
                                            echo "<option value='" . htmlspecialchars($rol['id_rol']) . "'>" . htmlspecialchars($rol['rol']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un rol.
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id_acceso" value="<?= htmlspecialchars($fila['id_acceso']) ?>">

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

<script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
