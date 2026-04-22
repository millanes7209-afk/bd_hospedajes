<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

if (isset($_GET['numero']) && isset($_GET['tipo']) && isset($_GET['precio'])) {
    $isReserva = isset($_GET['reservaID']) && isset($_GET['clienteID']);

if ($isReserva) {
    // Datos de la reserva
    $reservaID = $_GET['reservaID'];
    $clienteID = $_GET['clienteID'];
    $monto_total = isset($_GET['monto_total']) ? $_GET['monto_total'] : 0;
    $monto_pagado = isset($_GET['monto_pagado']) ? $_GET['monto_pagado'] : 0;    
    $monto_pendiente = isset($_GET['monto_pendiente']) ? $_GET['monto_pendiente'] : $precio_habitacion - $monto_pendiente;
} 

    // Obtén los valores de los parámetros
    $habitacion_numero = $_GET['numero'];
    $tipo_habitacion = $_GET['tipo'];
    $precio_habitacion = $_GET['precio'];
    
    // Consulta para obtener el ID de la habitación basado en el número
    $sql = "SELECT habitacionID FROM habitaciones WHERE numero = ?";
    $stmt = $db->Prepare($sql);
    $rs = $db->Execute($stmt, array($habitacion_numero));

    if ($rs && !$rs->EOF) {
        $habitacionID = $rs->fields['habitacionID'];
    } else {
        echo "<p>Error: No se encontró la habitación con el número especificado.</p>";
        exit;
    }
} else {
    echo "<p>No se han recibido los datos correctamente.</p>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Hospedaje</title>
    <script src="../js/validacion_obligatorios.js"></script>
    <script type='text/javascript' src='../../ajax.js'></script>
        <style>
             .mostrar {
            display: block !important; /* Asegura que se muestre siempre */
        }
        </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>AGREGAR HOSPEDAJE</h3>
                    </div>
                    <div class="card-body">
                        <!-- buscador -->
                            <h4>Seleccione un cliente</h4>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="ci" class="form-label">C.I.</label>
                                    <input type="text" class="form-control" name="ci" id="ci" onkeyup="buscarCliente()">
                                </div>
                            </div>
                            <div id="resultadosBusqueda"></div>
                            <div id="mensajeExito" class="alert alert-success" style="display:none;"></div>
                            <div id="mensajeAlertaCliente" class="alert alert-danger" style="display: none;"></div>

                            <div id="formularioRegistro" style="display: none;">
                                <!-- Formulario para registrar un nuevo cliente -->
                                <form id="formCliente" class="needs-validation" novalidate>
                                    <h4>Agregar Cliente</h4>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="ci1" class="form-label">(*) C.I.</label>
                                            <input type="text" class="form-control" name="ci1" id="ci1" required>
                                            <div class="invalid-feedback">Documento de identidad obligatorio.</div>
                                            <div id="mensajeAlerta" class="alert" style="display:none;"></div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="nombres1" class="form-label">(*) Nombres</label>
                                            <input type="text" class="form-control" name="nombres1" id="nombres1" required
                                            pattern="^[A-Za-zÀ-ÿ\s]+$" onkeyup="this.value=this.value.toUpperCase()">
                                            <div class="invalid-feedback">Ingrese solo texto.</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="apellidos1" class="form-label">(*) Apellidos</label>
                                            <input type="text" class="form-control" name="apellidos1" id="apellidos1" required
                                            pattern="^[A-Za-zÀ-ÿ\s]+$" onkeyup="this.value=this.value.toUpperCase()">
                                            <div class="invalid-feedback">Ingrese solo texto.</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="fecha_nacimiento1" class="form-label">(*) Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" name="fecha_nacimiento1" id="fecha_nacimiento1" onchange="validarEdad()" required>
                                            <div class="invalid-feedback">Fecha nacimiento obligatoria.</div>
                                            <div id="mensajeAlertas" class="alert" style="display:none;"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lugar_nacimiento1" class="form-label">(*) Lugar de Nacimiento</label>
                                            <input type="text" class="form-control" name="lugar_nacimiento1" id="lugar_nacimiento1" required
                                            onkeyup="this.value=this.value.toUpperCase()">
                                            <div class="invalid-feedback"> Lugar de Nacimiento obligatorio.</div>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="estado_civil1" class="form-label">Estado Civil</label>
                                            <input type="text" class="form-control" name="estado_civil1" id="estado_civil1" size="10" 
                                            pattern="[A-Za-z\s]+" onkeyup="this.value=this.value.toUpperCase()">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="profesion1" class="form-label">Profesión</label>
                                            <input type="text" class="form-control" name="profesion1" id="profesion1" size="10" 
                                                onkeyup="this.value=this.value.toUpperCase()">
                                        </div>
                                    </div>
                                    <div class="text-center">
                                    <button type="button" class="btn btn-primary" onclick="registrarCliente(event)">Agregar Cliente</button>

                                    </div>
                                </form>

                <!-- formulario hospedaje-->
                                </div>
                                <form id="formHospedaje" class="needs-validation" novalidate action="hospedaje_reserva1.php" method="post" name="formu" style="display: block;">
                                    <!-- Campos ocultos para la reserva -->
                            <input type="hidden" name="reservaID" id="reservaID" value="<?php echo $reservaID; ?>">
                            <input type="hidden" name="clienteID" id="clienteID" value="<?php echo $clienteID; ?>">
                            <input type="hidden" name="habitacionID" id="habitacionID" value="<?php echo $habitacionID; ?>">

                            <h3>HOSPEDAJE</h3>
                            <div id="mensajeAlertaClienteHospedaje" class="alert alert-danger" style="display: none;"></div>

                            <div id="contenedorClientesSeleccionados">
                                <!-- Aquí se añadirán los inputs ocultos para los clientes seleccionados -->
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="formaPagoID" class="form-label">(*) Forma de Pago</label>
                                    <select class="form-control" name="formaPagoID" id="formaPagoID">
                                        <option value="null">Seleccione una forma de pago</option>
                                        <?php
                                        // Cargar opciones de personas desde la base de datos
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
                                <div class="col-md-6">
                                    <label for="habitacionID" class="form-label">Número de habitación</label>
                                    <select class="form-control" name="habitacionID" id="habitacionID" disabled>
                                        <option value="<?php echo $habitacionID; ?>"><?php echo $habitacion_numero; ?></option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Este campo es obligatorio.
                                    </div>
                                </div>  
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="checkout" class="form-label"><b>(*) Salida</b></label>
                                    <input type="datetime-local" class="form-control" name="checkout" id="checkout" required>
                                    <div class="invalid-feedback">
                                        Fecha salida obligatoria.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto_total" class="form-label"><b>Monto Reserva</b></label>
                                    <input type="number" class="form-control" name="monto_total" id="monto_total" 
                                    value="<?php echo $precio_habitacion; ?>" readonly>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                            <div class="col-md-6">
                                    <label for="monto_pagado" class="form-label"><b>Monto Pagado</b></label>
                                    <input type="number" class="form-control" name="monto_pagado" id="monto_pagado" 
                                    value="<?php echo $monto_pagado; ?>" readonly>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="monto_pendiente" class="form-label"><b>Total</b></label>
                                    <input type="number" class="form-control" name="monto_pendiente" id="monto_pendiente" 
                                    value="<?php echo $precio_habitacion; ?>" readonly>
                                    <div class="invalid-feedback">
                                        Campo obligatorio.
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12 text-center">
                                    <button class="btn btn-primary" type="submit">Registrar</button>
                                    <button class="btn btn-secondary" type="button" onclick="window.history.back();">Atrás</button>

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

<script>
    function actualizarSalida() {
    var duracion = document.getElementById('duracion').value;
    var checkoutInput = document.getElementById('checkout');
    var horaIngreso = new Date(); // Obtiene la hora actual
    var precioHabitacion = document.getElementById('monto_total').value;
    var montoTotalInput = document.getElementById('monto_pendiente');

    if (duracion > 0) {
        // Si se ingresó antes de las 12 PM
        if (horaIngreso.getHours() < 12) {
            var fechaSalida = new Date();
            fechaSalida.setDate(fechaSalida.getDate() + parseInt(duracion));
            checkoutInput.value = `${fechaSalida.getFullYear()}-${String(fechaSalida.getMonth() + 1).padStart(2, '0')}-${String(fechaSalida.getDate()).padStart(2, '0')}T12:00`;
        } else {
            var fechaSalida = new Date();
            fechaSalida.setDate(fechaSalida.getDate() + parseInt(duracion));
            checkoutInput.value = `${fechaSalida.getFullYear()}-${String(fechaSalida.getMonth() + 1).padStart(2, '0')}-${String(fechaSalida.getDate()).padStart(2, '0')}T12:00`;
        }

        // Calcular el monto total
        var montoTotal = duracion * precioHabitacion;
        montoTotalInput.value = montoTotal // Redondear a dos decimales
    } else {
        // Si la duración está vacía, se restablece el valor original del precio de la habitación
        checkoutInput.value = ''; // Resetea el campo de salida si la duración no es válida
        montoTotalInput.value = precioHabitacion; // Restaura el valor predeterminado del precio de la habitación
    }
}
function buscarCliente() {
    // Ocultar el mensaje de alerta al comenzar una nueva búsqueda
    document.getElementById('mensajeAlertaCliente').style.display = 'none';
    document.getElementById('mensajeExito').style.display = 'none';

    var ci = document.getElementById('ci').value;
    if (ci.length > 0) {
        var ajax = nuevoAjax();
        var url = 'buscar_cliente.php';
        var param = 'ci=' + ci;
        ajax.open('POST', url, true);
        ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        ajax.onreadystatechange = function() {
            if (ajax.readyState == 4) {
                document.getElementById('resultadosBusqueda').innerHTML = ajax.responseText;

                // Solo afecta al formulario de registro de cliente
                if (ajax.responseText.indexOf('No se encontraron') !== -1) {
                    document.getElementById('formularioRegistro').style.display = 'block';
                } else {
                    document.getElementById('formularioRegistro').style.display = 'none';
                }

                // El formulario de hospedaje debe estar siempre visible, no se modifica aquí
            }
        }
        ajax.send(param);
    }
}

    function validarEdad() {
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento1');
    const fechaNacimiento = new Date(fechaNacimientoInput.value);
    const hoy = new Date();
    // Ajuste de la fecha de nacimiento para evitar problemas de zona horaria
    fechaNacimiento.setMinutes(fechaNacimiento.getMinutes() + fechaNacimiento.getTimezoneOffset());
    if (fechaNacimiento > hoy) {
        const mensajeAlerta = document.getElementById('mensajeAlertas');
        mensajeAlerta.className = 'alert alert-danger';
        mensajeAlerta.innerHTML = 'La fecha de nacimiento no puede ser posterior a la fecha actual.';
        mensajeAlerta.style.display = 'block';

        // Limpiar el campo de fecha si es posterior a hoy
        fechaNacimientoInput.value = '';
        return;
    } else {
        // Ocultar el mensaje de alerta si la fecha es válida
        document.getElementById('mensajeAlertas').style.display = 'none';
    }

    // Calcular la diferencia de años
    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();

    // Ajustar edad si el cumpleaños no ha ocurrido aún este año
    if (
        hoy.getMonth() < fechaNacimiento.getMonth() ||
        (hoy.getMonth() === fechaNacimiento.getMonth() && hoy.getDate() < fechaNacimiento.getDate())
    ) {
        edad--;
    }

    // Mostrar alerta si la edad es menor a 18 años
    const mensajeAlerta = document.getElementById('mensajeAlertas');
    if (edad < 18) {
        mensajeAlerta.className = 'alert alert-warning';
        mensajeAlerta.innerHTML = `El cliente tiene ${edad} años. Proceda con precaución.`;
        mensajeAlerta.style.display = 'block';
    } else {
        mensajeAlerta.style.display = 'none';
    }
}

function registrarCliente(event) {
    event.preventDefault(); // Evitar la recarga de la página

    var formCliente = document.getElementById('formCliente');
    
    // Validar el formulario del cliente
    if (!formCliente.checkValidity()) {
        formCliente.classList.add('was-validated');
        return;
    }

    // Proceder con el envío AJAX si la validación es correcta
    var ajax = nuevoAjax();
    var url = 'registrar_cliente.php';
    var ci = document.getElementById('ci1').value;
    var nombres = document.getElementById('nombres1').value;
    var apellidos = document.getElementById('apellidos1').value;
    var fechaNacimiento = document.getElementById('fecha_nacimiento1').value;
    var lugarNacimiento = document.getElementById('lugar_nacimiento1').value;
    var estadoCivil = document.getElementById('estado_civil1').value;
    var profesion = document.getElementById('profesion1').value;

    var param = `ci=${ci}&nombres=${nombres}&apellidos=${apellidos}&fecha_nacimiento=${fechaNacimiento}&lugar_nacimiento=${lugarNacimiento}&estado_civil=${estadoCivil}&profesion=${profesion}`;

    ajax.open('POST', url, true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4) {
            var mensajeAlerta = document.getElementById('mensajeAlerta');
            
            // Si el C.I. ya existe, mostrar error
            if (ajax.responseText === 'error_ci_duplicado') {
                mensajeAlerta.className = 'alert alert-danger';
                mensajeAlerta.innerHTML = 'El documento de identidad ya se encuentra registrado. Ingrese uno diferente.';
                mensajeAlerta.style.display = 'block';
            } 
            // Si la respuesta empieza con "error:", mostrar mensaje de error específico
            else if (ajax.responseText.startsWith('error:')) {
                mensajeAlerta.className = 'alert alert-warning';
                mensajeAlerta.innerHTML = ajax.responseText.replace('error: ', '');
                mensajeAlerta.style.display = 'block';
            } 
            // Si la respuesta empieza con "success:", manejar el registro exitoso
            else if (ajax.responseText.startsWith('success:')) {
                var clienteID = ajax.responseText.split(':')[1];
                var nombreCompleto = `${nombres} ${apellidos}`;

                // Añadir el cliente al campo oculto y al checklist
                seleccionarCliente(clienteID, nombreCompleto);

                // Mostrar mensaje de éxito
                var mensajeExito = document.getElementById('mensajeExito');
                mensajeExito.className = 'alert alert-success';
                mensajeExito.innerHTML = 'Cliente registrado exitosamente.';
                mensajeExito.style.display = 'block';

                // Limpiar el formulario de cliente
                formCliente.reset();
                formCliente.classList.remove('was-validated');
                document.getElementById('formularioRegistro').style.display = 'none';
                document.getElementById('resultadosBusqueda').innerHTML = '';
                document.getElementById('mensajeAlerta').style.display = 'none';
                document.getElementById('mensajeAlertas').style.display = 'none'; // Limpiar alerta de edad
                
            } 
            // Manejar otros errores genéricos
            else {
                mensajeAlerta.className = 'alert alert-warning';
                mensajeAlerta.innerHTML = 'Ha ocurrido un error desconocido. Respuesta del servidor: ' + ajax.responseText;
                mensajeAlerta.style.display = 'block';
            }
        }
    }
    ajax.send(param);
}


function seleccionarCliente(clienteID) {
    // Comprobar si el cliente ya está añadido
    if (!document.getElementById(`inputCliente_${clienteID}`)) {
        // Primero, validar si el cliente tiene un hospedaje activo
        var ajaxValidar = nuevoAjax();
        var urlValidar = 'hospedaje_activo.php';
        var paramValidar = 'clienteID=' + clienteID;

        ajaxValidar.open('POST', urlValidar, true);
        ajaxValidar.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        ajaxValidar.onreadystatechange = function() {
            if (ajaxValidar.readyState == 4 && ajaxValidar.status == 200) {
                // Limpiar la respuesta eliminando espacios en blanco
                var response = ajaxValidar.responseText.trim();

                // Verificar si hubo un error desde PHP
                if (response.startsWith('error:')) {
                    // Mostrar error detallado desde el servidor
                    var mensajeAlertaCliente = document.getElementById('mensajeAlertaCliente');
                    mensajeAlertaCliente.className = 'alert alert-danger';
                    mensajeAlertaCliente.innerHTML = response.replace('error:', '');
                    mensajeAlertaCliente.style.display = 'block';
                } else if (response === 'hospedaje_activo') {
                    // Mostrar mensaje usando Bootstrap alert para indicar hospedaje activo
                    var mensajeAlertaCliente = document.getElementById('mensajeAlertaCliente');
                    mensajeAlertaCliente.className = 'alert alert-danger';
                    mensajeAlertaCliente.innerHTML = 'El cliente ya se encuentra actualmente hospedado.';
                    mensajeAlertaCliente.style.display = 'block';
                } else if (response === 'no_hospedaje_activo') {
                    // Crear un nuevo input oculto para el cliente seleccionado
                    var contenedor = document.getElementById('contenedorClientesSeleccionados');

                    // Input oculto para el cliente seleccionado (se guarda en un array)
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'clientesSeleccionados[]'; // Array de clientes seleccionados
                    input.id = `inputCliente_${clienteID}`;
                    input.value = clienteID;

                    contenedor.appendChild(input);
                    
                    // Petición AJAX para obtener los detalles del cliente por clienteID
                    var ajax = nuevoAjax();
                    var url = 'seleccionar_clienteID.php';
                    var param = 'clienteID=' + clienteID;

                    ajax.open('POST', url, true);
                    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    ajax.onreadystatechange = function() {
                        if (ajax.readyState == 4 && ajax.status == 200) {
                            // Mostrar en la interfaz al cliente seleccionado con opción para deseleccionar
                            var checklistItem = document.createElement('div');
                            checklistItem.id = `checklistCliente_${clienteID}`;
                            checklistItem.innerHTML = `
                                <input type="checkbox" checked onchange="deseleccionarCliente(${clienteID})">
                                ${ajax.responseText} <!-- Aquí se muestra el nombre y CI del cliente -->
                            `;
                            contenedor.appendChild(checklistItem);
                                                        // Limpiar el campo de búsqueda del C.I. y los resultados de búsqueda
document.getElementById('ci').value = '';
document.getElementById('resultadosBusqueda').innerHTML = '';
                        }
                    };
                    ajax.send(param);
                }
            } else if (ajaxValidar.readyState == 4) {
                // Mostrar error si la petición no fue exitosa
                var mensajeAlertaCliente = document.getElementById('mensajeAlertaCliente');
                mensajeAlertaCliente.className = 'alert alert-danger';
                mensajeAlertaCliente.innerHTML = 'Error al comunicarse con el servidor para validar el hospedaje.';
                mensajeAlertaCliente.style.display = 'block';
            }
        };
        ajaxValidar.send(paramValidar);
    }
}



function deseleccionarCliente(clienteID) {
    // Eliminar el input oculto
    var input = document.getElementById(`inputCliente_${clienteID}`);
    if (input) {
        input.remove();
    }

    // Eliminar el elemento visual del checklist
    var checklistItem = document.getElementById(`checklistCliente_${clienteID}`);
    if (checklistItem) {
        checklistItem.remove();
    }
}


</script>
<script>
    // Añadir un listener al formulario de hospedaje para validar antes de enviar
document.getElementById('formHospedaje').addEventListener('submit', function(event) {
    // Verificar si hay al menos un cliente seleccionado
    var contenedorClientesSeleccionados = document.getElementById('contenedorClientesSeleccionados');
    if (contenedorClientesSeleccionados.children.length === 0) {
        // Evitar el envío del formulario si no hay clientes seleccionados
        event.preventDefault();
        
        // Mostrar mensaje de error utilizando Bootstrap
        var mensajeAlertaCliente = document.getElementById('mensajeAlertaClienteHospedaje');
        mensajeAlertaCliente.className = 'alert alert-danger';
        mensajeAlertaCliente.innerHTML = 'Debe agregar al menos un cliente antes de registrar el hospedaje.';
        mensajeAlertaCliente.style.display = 'block';
    }
});
document.addEventListener("DOMContentLoaded", function() {
    const isReserva = <?php echo $isReserva ? 'true' : 'false'; ?>;
    const clienteID = "<?php echo $clienteID; ?>";
    const reservaID = "<?php echo $reservaID; ?>";
    const montoTotal = "<?php echo $monto_total; ?>";
    const montoPendiente = "<?php echo $monto_pendiente; ?>";

    if (isReserva) {
        // Autollenar campos desde la reserva
        document.getElementById("monto_total").value = montoTotal;
        document.getElementById("monto_pendiente").value = montoPendiente;
        document.getElementById("reservaID").value = reservaID;
        document.getElementById("clienteID").value = clienteID;

        // Seleccionar automáticamente al cliente asociado
        if (clienteID) {
            seleccionarCliente(clienteID);
        }
    }
});

</script>
</body>
</html>
