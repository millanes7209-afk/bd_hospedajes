<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inserción de Movimiento de Sueldo</title>

    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }
        .card {
            margin: 20px;
        }
        tr {
            color: black;
        }
        .form-control {
            border-color: black;
        }
        .form-check {
            margin-right: 10px;
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
                            <h3>AGREGAR MOVIMIENTO DE SUELDO</h3>
                        </div>
                        <div class="card-body">
                            <form class="needs-validation" novalidate action="movimiento_sueldo_guardar.php" method="post" name="formu">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="tipo_movimiento" class="form-label">(*) Tipo de Movimiento</label>
                                        <select class="form-control" name="tipo_movimiento" id="tipo_movimiento" required>
                                            <option value="">Seleccione el tipo de movimiento</option>
                                            <option value="SUELDO">CANCELAR SUELDO</option>
                                            <option value="ADELANTO">ADELANTO</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="id_persona" class="form-label">(*) Empleado</label>
                                        <select class="form-control" name="id_persona" id="id_persona" required>
                                            <option value="">Seleccione una empleado</option>
                                            <?php
                                            // Cargar opciones de personas desde la base de datos
                                            $sql_personas = $db->Prepare("SELECT id_persona, CONCAT(ap, ' ', am, ' ', nombres) AS nombre_completo FROM personas");
                                            $rs_personas = $db->GetAll($sql_personas);
                                            foreach ($rs_personas as $persona) {
                                                echo "<option value='{$persona['id_persona']}'>{$persona['nombre_completo']}</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="monto" class="form-label">(*) Monto</label>
                                        <input type="number" class="form-control" name="monto" id="monto" step="0.01" min="0" required>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="fecha_hora" class="form-label">(*) Fecha y Hora</label>
                                        <input type="datetime-local" class="form-control" name="fecha_hora" id="fecha_hora" required>
                                        <div class="invalid-feedback">
                                            Este campo es obligatorio.
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="id_persona" value="<?php echo htmlspecialchars($_SESSION['id_usuario']); ?>">
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
