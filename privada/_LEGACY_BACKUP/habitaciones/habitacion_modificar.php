<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Obtener el ID de la habitación que se va a modificar
$habitacionID = $_POST["habitacionID"];

// Consulta SQL para obtener los datos de la habitación
$sql = $db->Prepare("SELECT h.*, th.tipo 
                     FROM habitaciones h
                     JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
                     WHERE h.habitacionID = ?
                     AND h._estado <> 'X'");
$rs = $db->GetAll($sql, array($habitacionID));

// Consulta para obtener todos los tipos de habitación disponibles
$sqlTipos = $db->Prepare("SELECT tipohabitacionID, tipo FROM tipo_habitaciones WHERE _estado <> 'X'");
$rsTipos = $db->GetAll($sqlTipos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Habitación</title>
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
                            <h3>MODIFICAR HABITACIÓN</h3>
                        </div>
                        <div class="card-body">
                        <?php foreach ($rs as $fila) { ?>
                        <form class="needs-validation" novalidate action="habitacion_modificar1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="numero" class="form-label">(*) Número</label>
                                    <input type="text" class="form-control" name="numero" id="numero" required
                                        value="<?= htmlspecialchars($fila['numero']) ?>">
                                    <div class="invalid-feedback">
                                        Ingrese el número de la habitación
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="tipo" class="form-label">Tipo</label>
                                    <select name="tipohabitacionID" class="form-control" required>
                                        <?php foreach ($rsTipos as $tipo) { ?>
                                            <option value="<?= $tipo['tipohabitacionID'] ?>" 
                                                <?= $tipo['tipohabitacionID'] == $fila['tipohabitacionID'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($tipo['tipo']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select name="estado" class="form-control" required>
                                        <?php
                                        // Lista de los posibles estados
                                        $estados = ['DISPONIBLE', 'OCUPADA', 'RESERVADA', 'MANTENIMIENTO', 'DEUDA', 'LIMPIEZA', 'MOMENTANEO'];

                                        // Recorremos la lista de posibles estados
                                        foreach ($estados as $estado) { ?>
                                            <option value="<?= $estado ?>" 
                                                <?= $estado == $rs[0]['estado'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($estado) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tv" class="form-label">TV</label><br>
                                    <input type="checkbox" name="tv" id="tv" value="1" <?= $fila['tv'] ? 'checked' : '' ?>>
                                    <label for="tv">¿Tiene TV?</label>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="bano" class="form-label">Baño</label><br>
                                    <input type="checkbox" name="bano" id="bano" value="1" <?= $fila['bano'] ? 'checked' : '' ?>>
                                    <label for="bano">¿Tiene Baño?</label>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <input type="hidden" name="habitacionID" value="<?= htmlspecialchars($fila['habitacionID']) ?>">
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
            </div>
        </div>
    </div>
    <script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
