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
    <title>Agregar incidente</title>
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
        <div class="col-md-6">
            <div class="form-container">
                <div class="card">
                    <div class="card-header">
                        <h3>AGREGAR incidente</h3>
                    </div>
                    <div class="card-body">
                        <!-- buscador -->
                            <h4>Seleccione un cliente</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ci" class="form-label">C.I.</label>
                                    <input type="text" class="form-control" name="ci" id="ci" onkeyup="buscarCliente()">
                                </div>
                            </div>
                            <div id="resultadosBusqueda"></div>
                            <div id="mensajeExito" class="alert alert-success" style="display:none;"></div>
                            <div id="mensajeAlertaCliente" class="alert alert-danger" style="display: none;"></div>

                            <div id="formularioRegistro" style="display: none;">
                                
                <!-- formulario incidente-->
                                </div>
                                <form id="formIncidente" class="needs-validation" novalidate action="incidente_nuevo1.php" method="post" name="formu" style="display: block;">
                            <h3>INCIDENTE</h3>
                            <div id="mensajeAlertaClienteincidente" class="alert alert-danger" style="display: none;"></div>

                            <div id="contenedorClientesSeleccionados">
                                <!-- Aquí se añadirán los inputs ocultos para los clientes seleccionados -->
                            </div>  
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" id="descripcion" rows="4" required></textarea>
                                    <div class="invalid-feedback">
                                        Por favor, ingresa una descripción válida.
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

                // El formulario de incidente debe estar siempre visible, no se modifica aquí
            }
        }
        ajax.send(param);
    }
}

function seleccionarCliente(clienteID) {
    // Comprobar si el cliente ya está añadido
    if (!document.getElementById(`inputCliente_${clienteID}`)) {
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
    // Añadir un listener al formulario de incidente para validar antes de enviar
document.getElementById('formIncidente').addEventListener('submit', function(event) {
    // Verificar si hay al menos un cliente seleccionado
    var contenedorClientesSeleccionados = document.getElementById('contenedorClientesSeleccionados');
    if (contenedorClientesSeleccionados.children.length === 0) {
        // Evitar el envío del formulario si no hay clientes seleccionados
        event.preventDefault();
        
        // Mostrar mensaje de error utilizando Bootstrap
        var mensajeAlertaCliente = document.getElementById('mensajeAlertaClienteincidente');
        mensajeAlertaCliente.className = 'alert alert-danger';
        mensajeAlertaCliente.innerHTML = 'Debe agregar al menos un cliente antes de registrar el incidente.';
        mensajeAlertaCliente.style.display = 'block';
    }
});
function checkOtros(selectElement) {
    var otroInput = document.getElementById('otro_lugar_nacimiento');
    if (selectElement.value === 'otros') {
        otroInput.style.display = 'block';
        otroInput.required = true;
    } else {
        otroInput.style.display = 'none';
        otroInput.required = false;
    }
}
</script>
</body>
</html>
