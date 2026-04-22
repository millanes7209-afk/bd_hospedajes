/**
 * Funciones de búsqueda y selección de clientes para Hospedaje
 */

function buscarCliente() {
    var mensajeAlerta = document.getElementById('mensajeAlertaCliente');
    var resBusqueda = document.getElementById('resultadosBusqueda');
    var frmRegistro = document.getElementById('formularioRegistro');
    
    if (mensajeAlerta) mensajeAlerta.style.display = 'none';
    if (resBusqueda) resBusqueda.innerHTML = ''; 

    var ci = document.getElementById('ci').value;
    var paisID = document.getElementById('paisID').value;
    
    if (ci.trim().length === 0) {
        if (mensajeAlerta) {
            mensajeAlerta.innerHTML = "Ingrese C.I. para buscar.";
            mensajeAlerta.style.display = 'block';
        }
        return;
    }

    var ajax = nuevoAjax();
    var url = 'buscar_cliente.php';
    var param = 'ci=' + encodeURIComponent(ci) + '&paisID=' + encodeURIComponent(paisID);
    
    ajax.open('POST', url, true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4 && ajax.status == 200) {
            var respuesta = ajax.responseText;
            resBusqueda.innerHTML = respuesta;

             if (respuesta.indexOf('Cliente no encontrado') !== -1) {
                var selectPais = document.getElementById('paisID');
                var nombrePais = selectPais.options[selectPais.selectedIndex].text;
                
                // LIMPIAR FORMULARIO PREVIAMENTE (por si había datos de una búsqueda anterior)
                var formCliente = document.getElementById('formCliente');
                if (formCliente) {
                    var campos = formCliente.querySelectorAll('input, select');
                    campos.forEach(c => { if(c.type !== 'hidden') c.value = ''; });
                    formCliente.classList.remove('was-validated');
                }
                if (document.getElementById('edadDisplay')) document.getElementById('edadDisplay').style.display = 'none';
                if (document.getElementById('mensajeAlertas')) document.getElementById('mensajeAlertas').style.display = 'none';

                if (frmRegistro) frmRegistro.style.display = 'block';
                // Poblar campos de texto y ocultos
                if (document.getElementById('ci1')) document.getElementById('ci1').value = ci;
                if (document.getElementById('ci1_display')) document.getElementById('ci1_display').innerText = ci;
                if (document.getElementById('paisID1')) document.getElementById('paisID1').value = paisID;
                if (document.getElementById('paisID1_text')) document.getElementById('paisID1_text').innerText = nombrePais;
                
                // Lógica de lugar de nacimiento DINÁMICA
                var contenedorLugar = document.getElementById('contenedorLugarNacimiento');
                if (nombrePais.toUpperCase() === 'BOLIVIA') {
                    contenedorLugar.innerHTML = `
                        <select class="form-control form-control-sm" name="lugar_nacimiento1" id="lugar_nacimiento1" required>
                            <option value="" selected disabled>Seleccione...</option>
                            <option value="BENI">BENI</option>
                            <option value="CHUQUISACA">CHUQUISACA</option>
                            <option value="COCHABAMBA">COCHABAMBA</option>
                            <option value="LA PAZ">LA PAZ</option>
                            <option value="ORURO">ORURO</option>
                            <option value="PANDO">PANDO</option>
                            <option value="POTOSÍ">POTOSÍ</option>
                            <option value="SANTA CRUZ">SANTA CRUZ</option>
                            <option value="TARIJA">TARIJA</option>
                        </select>`;
                } else {
                    contenedorLugar.innerHTML = `
                        <input type="text" class="form-control form-control-sm bg-white" name="lugar_nacimiento1" id="lugar_nacimiento1" required 
                               value="${nombrePais.toUpperCase()}" onkeyup="this.value=this.value.toUpperCase()">`;
                }

                document.getElementById('nombres1').focus();
                
                // Refrescar sugerencias de profesiones desde memoria local
                if (typeof cargarProfesionesDeMemoria === 'function') {
                    cargarProfesionesDeMemoria();
                }
            } else {
                if (frmRegistro) frmRegistro.style.display = 'none';
            }
        }
    }
    ajax.send(param);
}

function seleccionarCliente(clienteID) {
    if (document.getElementById('itemCliente_' + clienteID)) {
        // En lugar de alert, usamos el div de error del buscador
        var mensajeAlerta = document.getElementById('mensajeAlertaCliente');
        if (mensajeAlerta) {
            mensajeAlerta.innerHTML = "Este cliente ya está seleccionado.";
            mensajeAlerta.style.display = 'block';
        }
        return;
    }

    var ajax = nuevoAjax();
    var url = 'seleccionar_clienteID.php';
    var param = 'clienteID=' + clienteID;

    ajax.open('POST', url, true);
    ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    ajax.onreadystatechange = function() {
        if (ajax.readyState == 4 && ajax.status == 200) {
            var lista = document.getElementById('listaClientesSeleccionados');
            var contenedorPadre = document.getElementById('cardClientesSeleccionados');
            
            contenedorPadre.style.display = 'block';

            var inputOculto = document.createElement('input');
            inputOculto.type = 'hidden';
            inputOculto.name = 'clientesSeleccionados[]';
            inputOculto.value = clienteID;
            inputOculto.id = 'inputCliente_' + clienteID;
            
            var item = document.createElement('div');
            item.id = 'itemCliente_' + clienteID;
            item.className = 'list-group-item list-group-item-action border-0 border-bottom py-2';
            item.innerHTML = ajax.responseText;
            item.appendChild(inputOculto);

            lista.appendChild(item);
            
            if (document.getElementById('resultadosBusqueda')) document.getElementById('resultadosBusqueda').innerHTML = '';
            if (document.getElementById('ci')) document.getElementById('ci').value = '';
        }
    };
    ajax.send(param);
}

function deseleccionarCliente(clienteID) {
    var element = document.getElementById('itemCliente_' + clienteID);
    if (element) {
        element.remove();
    }
    
    var lista = document.getElementById('listaClientesSeleccionados');
    if (lista.children.length === 0) {
        document.getElementById('cardClientesSeleccionados').style.display = 'none';
    }
}
