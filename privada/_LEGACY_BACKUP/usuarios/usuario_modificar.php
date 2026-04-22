<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

$empleadoID = $_POST["empleadoID"];
$usuarioID = $_POST["usuarioID"];

// Consulta para obtener información del usuario
$sql = $db->Prepare("SELECT *
                     FROM usuarios
                     WHERE usuarioID = ?
                     AND _estado = 'A'");
$rs = $db->GetAll($sql, array($usuarioID));

// Consulta para obtener el empleado asociado al usuario
$sql1 = $db->Prepare("SELECT CONCAT_WS(' ', apellidos, nombres) as empleado, empleadoID
                     FROM empleados
                     WHERE empleadoID = ?
                     AND _estado = 'A'");
$rs1 = $db->GetAll($sql1, array($empleadoID));

// Consulta para obtener los empleados que no están asociados al usuario
$sql2 = $db->Prepare("SELECT CONCAT_WS(' ', apellidos, nombres) as empleado, empleadoID
                     FROM empleados
                     WHERE empleadoID <> ?
                     AND _estado = 'A'");
$rs2 = $db->GetAll($sql2, array($empleadoID));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario</title>
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
        <div class="col-md-6">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>MODIFICAR USUARIO</h3>
                    </div>
                    <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="usuario_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="empleadoID" class="form-label">(*) Empleado</label>
                                    <select class="form-control" name="empleadoID" id="empleadoID" required>
                                        <?php
                                        foreach ($rs1 as $empleado) {
                                            echo "<option value='" . htmlspecialchars($empleado['empleadoID']) . "'>" . htmlspecialchars($empleado['empleado']) . "</option>";
                                        }
                                        foreach ($rs2 as $empleado) {
                                            echo "<option value='" . htmlspecialchars($empleado['empleadoID']) . "'>" . htmlspecialchars($empleado['empleado']) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Seleccione un empleado.
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="usuario" class="form-label">(*)Usuario</label>
                                    <input type="text" class="form-control" name="usuario" id="usuario" value="<?= htmlspecialchars($fila['usuario']) ?>" required>
                                    <div class="invalid-feedback">
                                        Ingrese el nombre de usuario.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="clave" class="form-label">(*) Clave</label>
                                    <input type="password" class="form-control" name="clave" id="clave" required>
                                    <div class="invalid-feedback">
                                        Ingrese la clave.
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="usuarioID" value="<?= htmlspecialchars($fila['usuarioID']) ?>">

                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Modificar Usuario</button>
                                    <button class="btn btn-secondary" type="button" onclick="history.back()">Atrás</button>
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
