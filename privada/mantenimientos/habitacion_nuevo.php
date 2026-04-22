<?php
// Sección PHP: Lógica de servidor y consulta a la base de datos
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Consulta para obtener los tipos de habitaciones
$sqlTipos = $db->Prepare("
    SELECT tipohabitacionID, tipo
    FROM tipo_habitaciones
    WHERE _estado <> 'X'
");
$rsTipos = $db->GetAll($sqlTipos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Habitación</title>
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
        <div class="col-md-4">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>AGREGAR HABITACIÓN</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="habitacion_nuevo1.php" method="post" name="formu">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="numero" class="form-label">(*) Número de Habitación</label>
                                    <input type="number" class="form-control" name="numero" id="numero" required
                                    min="1" step="1">
                                    <div class="invalid-feedback">
                                        Número de habitación necesario.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="tipohabitacionID" class="form-label">(*) Tipo de Habitación</label>
                                    <select class="form-control" name="tipohabitacionID" id="tipohabitacionID" required>
                                        <option value="">Seleccione un tipo</option>
                                        <?php foreach ($rsTipos as $tipo) : ?>
                                            <option value="<?php echo $tipo['tipohabitacionID']; ?>"><?php echo $tipo['tipo']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Tipo de habitación necesario.
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <!-- Campo oculto con valor 0 -->
                                    <input type="hidden" name="tv" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tv" id="tv" value="1">
                                        <label class="form-check-label" for="tv">
                                            TV
                                        </label>
                                    </div>
                                    <div class="invalid-feedback">
                                        Indique si la habitación tiene TV.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <!-- Campo oculto con valor 0 -->
                                    <input type="hidden" name="bano" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="bano" id="bano" value="1">
                                        <label class="form-check-label" for="bano">
                                           Baño Privado
                                        </label>
                                    </div>
                                    <div class="invalid-feedback">
                                        Indique si la habitación tiene baño.
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <!-- Campo oculto con valor 0 -->
                                    <input type="hidden" name="ventilador" value="0">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="ventilador" id="ventilador" value="1">
                                        <label class="form-check-label" for="ventilador">
                                            Tiene Ventilador
                                        </label>
                                    </div>
                                    <div class="invalid-feedback">
                                        Indique si la habitación tiene ventilador.
                                    </div>
                                </div>
                            </div>
                            <br>
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Aceptar</button>
                                    <button class="btn btn-secondary" type="reset">Borrar</button>
                                    <br>
                                    <small>(*) Datos Obligatorios</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../js/validacion_obligatorios.js"></script>
</body>
</html>
