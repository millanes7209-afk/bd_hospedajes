<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$id_grupo = $_POST["id_grupo"];
$id_opcion = $_POST["id_opcion"];

// Consulta para obtener información del usuario
$sql = $db->Prepare("SELECT *
                     FROM   opciones
                     WHERE  id_opcion = ?
                     AND    _estado = 'A'
                     ");
$rs = $db->GetAll($sql, array($id_opcion));

// Consulta para obtener la persona asociada al usuario
$sql1 = $db->Prepare("SELECT  *, id_grupo
                     FROM     grupos
                     WHERE    id_grupo = ?
                     AND      _estado = 'A'
                     ");
$rs1 = $db->GetAll($sql1, array($id_grupo));

// Consulta para obtener las grupos que no están asociadas al usuario
$sql2 = $db->Prepare("SELECT  *, id_grupo
                     FROM     grupos
                     WHERE    id_grupo <> ?
                     AND      _estado = 'A'
                     ");
$rs2 = $db->GetAll($sql2, array($id_grupo));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Opción</title>
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
                        <h3>MODIFICAR OPCIÓN</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="opcion_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="id_grupo" class="form-label">(*) Grupo</label>
                                    <select class="form-control" name="id_grupo" id="id_grupo" required>
                                        <?php
                                        foreach ($rs1 as $grupo) {
                                            echo "<option value='" . htmlspecialchars($grupo['id_grupo']) . "'>" . htmlspecialchars($grupo['grupo']) . "</option>";
                                        }
                                        foreach ($rs2 as $grupo) {
                                            echo "<option value='" . htmlspecialchars($grupo['id_grupo']) . "'>" . htmlspecialchars($grupo['grupo']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un grupo.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="opcion" class="form-label">(*) </label>
                                    <input type="text" class="form-control" name="opcion" id="opcion" value="<?= htmlspecialchars($fila['opcion']) ?>" required>
                                    <div class="invalid-feedback">
                                        Ingrese el nombre de opcion.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="contenido" class="form-label">(*) Contenido</label>
                                    <input type="text" class="form-control" name="contenido" id="contenido" 
                                    value="<?= htmlspecialchars($fila['contenido']) ?>" required>
                                    <div class="invalid-feedback">
                                        Ingrese el nombre de contenido.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="orden" class="form-label">(*) Orden</label>
                                    <input type="text" class="form-control" name="orden" id="orden"
                                    value="<?= htmlspecialchars($fila['orden']) ?>" required>
                                    <div class="invalid-feedback">
                                        Ingrese la orden.
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="id_opcion" value="<?= htmlspecialchars($fila['id_opcion']) ?>">

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Modificar Usuario</button>
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
