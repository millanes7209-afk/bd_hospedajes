<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Si se ha enviado el ID de la habitación a través de la URL
if (isset($_GET['numero']) && isset($_GET['tipo']) && isset($_GET['precio'])) {
    // Obtén los valores de los parámetros
    $habitacion_numero = $_GET['numero'];
    $tipo_habitacion = $_GET['tipo'];
    $precio_habitacion = $_GET['precio'];

    // Consulta para obtener el ID de la habitación basado en el número
    $sql = "SELECT habitacionID FROM habitaciones WHERE numero = ?";
    $stmt = $db->Prepare($sql);
    $rs = $db->Execute($stmt, array($habitacion_numero));

    if ($rs && !$rs->EOF) {
        $habitacionID = $rs->fields['habitacionID']; // Recoger el ID de la habitación
    } else {
        // Manejar error si no se encuentra la habitación
        echo "<p>Error: No se encontró la habitación con el número especificado.</p>";
        exit;
    }
} else {
    // Muestra un mensaje de error si falta alguno de los parámetros
    echo "<p>No se han recibido los datos correctamente.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Reserva</title>
    <style>
        .card {
            margin: 20px;
        }
        .form-control {
            border-color: black;
        }
        .formita {
            padding: 25px;
        }
        thead {
            color: black;
            background: #b5b5b5;
        }
        tr {
            color: black;
        }
    </style>
    <script type='text/javascript' src='../../ajax.js'></script>
    
    <script type='text/javascript'>
        function buscar() {
            var d1, contenedor, url;
            contenedor = document.getElementById('personas');
            contenedor2 = document.getElementById('persona_seleccionado');
            contenedor3 = document.getElementById('persona_insertada');
            d1 = document.formu.ci.value;
            ajax = nuevoAjax();
            url = 'ajax_buscar_persona.php';
            param = 'ci=' + d1;
            ajax.open('POST', url, true);
            ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            ajax.onreadystatechange = function () {
                if (ajax.readyState == 4) {
                    contenedor.innerHTML = ajax.responseText;
                    contenedor2.innerHTML = '';
                    contenedor3.innerHTML = '';
                }
            }
            ajax.send(param);
        }

        function buscar_persona(clienteID) {
            var d1, contenedor, url;
            contenedor = document.getElementById('persona_seleccionado');
            contenedor2 = document.getElementById('personas');
            document.formu.clienteID.value = clienteID;
            d1 = clienteID;

            ajax = nuevoAjax();
            url = 'ajax_buscar_persona1.php';
            param = 'clienteID=' + d1;
            ajax.open('POST', url, true);
            ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            ajax.onreadystatechange = function () {
                if (ajax.readyState == 4) {
                    contenedor.innerHTML = ajax.responseText;
                    contenedor2.innerHTML = '';
                }
            }
            ajax.send(param);
        }

        function inserta_persona() {
            var d1, contenedor, url;
            contenedor = document.getElementById('persona_seleccionado');
            contenedor2 = document.getElementById('personas');
            contenedor3 = document.getElementById('persona_insertada');
            d1 = document.formu.ci1.value;
            d2 = document.formu.apellidos1.value;
            d3 = document.formu.nombres1.value;
            d5 = document.formu.lugar_nacimiento1.value;
            d4 = document.formu.fecha_nacimiento1.value;
            d6 = document.formu.estado_civil1.value;
            d7 = document.formu.profesion1.value;
            
            if (d1 == '') {
                alert('El ci es incorrecto o el campo esta vacio');
                document.formu.ci1.focus();
                return;
            }
            if (d2=='') {
                alert('Por favor introduzca los apellidos');
                document.formu.apellidos1.focus();
                return;
            }
            if (d3 == '') {
                alert('El nombre es incorrecto o el campo esta vacio');
                document.formu.nombres1.focus();
                return;
            }
            if (d5 == '') {
                alert('Por favor introduzca el lugar de nacimiento');
                document.formu.lugar_nacimiento1.focus();
                return;
            }
            if (d4 == '') {
                alert('Por favor introduzca la fecha de nacimiento');
                document.formu.fecha_nacimiento1.focus();
                return;
            }
            
            ajax=nuevoAjax();
            url='ajax_inserta_persona.php';
            param='ci1='+d1+'&apellidos1='+d2+'&nombres1='+d3+'&fecha_nacimiento1='+d4+'&lugar_nacimiento1='+d5+'&estado_civil1='+d6+'&profesion1='+d7;
            ajax.open('POST',url,true);
            ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            alert('REGISTRADO CORRECTAMENTE');
            ajax.onreadystatechange=function(){
                if(ajax.readyState==4){
                    contenedor.innerHTML=''; 
                    contenedor2.innerHTML=''; 
                    contenedor3.innerHTML=ajax.responseText; 
                }
            }
            ajax.send(param);
        }
    </script>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>AGREGAR RESERVA</h3>
                    </div>
                    <div class="card-body">
                        <form class="needs-validation" novalidate action="reserva_nuevo1.php" method="post" name="formu">
                            <h4>Seleccione un cliente</h4>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="ci" class="form-label">C.I.</label>
                                    <input type="text" class="form-control" name="ci" id="ci" onkeyup="buscar()">
                                </div>
                            </div>
                            <div class="mb-3">
                                <div id="personas"></div>
                            </div>
                            <div class="mb-3">
                                <div id="persona_seleccionado"></div>
                            </div>
                            <div class="mb-3">
                                <input type="hidden" name="clienteID">
                                <div id="persona_insertada"></div>
                            </div>
                            <h3>RESERVA</h3>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="formaPagoID" class="form-label">(*) Forma de Pago</label>
                                    <select class="form-control" name="formaPagoID" id="formaPagoID">
                                        <option value="null">Seleccione una forma de pago</option>
                                        <?php
                                        // Cargar opciones de formas de pago desde la base de datos
                                        $sql_formapago = $db->Prepare("SELECT formaPagoID,tipo FROM formas_pago WHERE _estado='A'");
                                        $rs_formapago = $db->GetAll($sql_formapago);
                                        foreach ($rs_formapago as $formapago) {
                                            echo "<option value='{$formapago['formaPagoID']}'>{$formapago['tipo']}</option>";
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
                                    <label for="habitacionID" class="form-label">(*) Número de habitación</label>
                                    <select class="form-control" id="habitacionID" disabled>
                                        <option value="<?php echo $habitacionID; ?>"><?php echo $habitacion_numero; ?></option>
                                    </select>
                                    <!-- Campo oculto para enviar el ID de la habitación -->
                                    <input type="hidden" name="habitacionID" value="<?php echo $habitacionID; ?>">
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="tipohabitacionID" class="form-label">(*) Tipo de habitación</label>
                                    <select class="form-control" name="tipohabitacionID" id="tipohabitacionID" disabled>
                                        <option value="<?php echo $tipo_habitacion; ?>"><?php echo $tipo_habitacion; ?></option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="checkin" class="form-label"><b>(*) Fecha llegada</b></label>
                                    <input type="datetime-local" class="form-control" name="checkin" id="checkin" required>
                                    <div class="invalid-feedback">
                                        Fecha de llegada obligatoria.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto_reserva" class="form-label"><b>(*)Precio de la habitación</b></label>
                                    <input type="number" class="form-control" name="monto_reserva" id="monto_reserva" oninput="calcular()"
                                    value="<?php echo $precio_habitacion; ?>" required min="1" step="0.01">
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="monto_pagado" class="form-label"><b>(*) Monto Pagado</b></label>
                                    <input type="number" class="form-control" name="monto_pagado" id="monto_pagado" 
                                    step="1" min="0" oninput="calcular()">
                                    <div class="invalid-feedback">
                                        Fecha check-in obligatoria.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto_pendiente" class="form-label"><b>(*) Monto Pendiente</b></label>
                                    <input type="number" class="form-control" name="monto_pendiente" id="monto_pendiente" 
                                    value="<?php echo $precio_habitacion; ?>" readonly>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                            </div>
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
<script>
    function calcular() {
        var montoReserva = parseFloat(document.getElementById('monto_reserva').value) || 0;
        var montoPagado = parseFloat(document.getElementById('monto_pagado').value) || 0;
        var montoPendiente = montoReserva - montoPagado;

        document.getElementById('monto_pendiente').value = montoPendiente;
    }
</script>
</body>
</html>
