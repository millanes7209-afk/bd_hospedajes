/**
 * LÓGICA DE NEGOCIO PARA EL MAPA INTERACTIVO DE HABITACIONES
 * Ubicación: privada/habitaciones/js/habitaciones_gestion.js
 */

function mostrarModalEgreso() {
    var modalEgreso = new bootstrap.Modal(document.getElementById('modal-egreso'));
    modalEgreso.show();
}

function mostrarModalIngreso() {
    var modalIngreso = new bootstrap.Modal(document.getElementById('modal-ingreso'));
    modalIngreso.show();
}

/**
 * CLICK EN HABITACIÓN - DETERMINA ACCIÓN SEGÚN ESTADO
 */
function handleHabitacionClick(estado, numero, tipo, precio, habitacionID) {
    var modal = new bootstrap.Modal(document.getElementById('menu-opciones'));
    const modalFooter = document.getElementById('modal-footer');
    modalFooter.innerHTML = ''; // Limpiar botones existentes
    
    document.getElementById('modal-body-text').innerHTML = `Habitación: ${numero} <br> Por favor elija una opción:`;

    if (estado === 'DISPONIBLE') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="hospedar_ocupar('hospedar', '${numero}', '${tipo}', '${precio}', '${habitacionID}')">HOSPEDAR</button>
            <button type="button" class="btn btn-info" onclick="momentaneo('${numero}', '${habitacionID}')">SIN CARNET</button>
        `;
        modal.show();
    } else if (estado === 'OCUPADA') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="mostrarModalPermanencia('${habitacionID}')">PERMANENCIA</button>
            <button type="button" class="btn btn-secondary" onclick="desocupar('${habitacionID}')">DESOCUPAR</button>
            <button type="button" class="btn btn-primary" onclick="agregar_huesped('aumentar', '${numero}', '${tipo}', '${habitacionID}')">AGREGAR HUESPED</button>
        `;
        modal.show();
    } else if (estado === 'LIMPIEZA') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-success" onclick="cambiarEstado('${habitacionID}', 'DISPONIBLE')">DISPONIBLE</button>
            <button type="button" class="btn btn-dark" onclick="mantenimiento('${numero}', '${habitacionID}')">MANTENIMIENTO</button>
        `;
        modal.show();
    } else if (estado === 'DEUDA') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="mostrarModalPermanencia('${habitacionID}', 'OCUPADA')">PAGAR Y OCUPAR</button>
            <button type="button" class="btn btn-secondary" onclick="mostrarModalPagoDeuda('${habitacionID}', 'LIMPIEZA')">PAGAR Y DESOCUPAR</button>
        `;
        modal.show();
    } else if (estado === 'RESERVADA') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="ocuparDesdeReserva('${habitacionID}', 'OCUPADA')">HOSPEDAR</button>
            <button type="button" class="btn btn-success" onclick="liberar('${habitacionID}')">CANCELAR</button>
        `;
        modal.show();
    } else if (estado === 'MOMENTANEO') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="desocupar1('${habitacionID}')">DESOCUPAR</button>
        `;
        modal.show();
    } else {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="cambiarEstado('${habitacionID}', 'LIMPIEZA')">LIMPIEZA</button>
        `;
        modal.show(); 
    }
}   

/**
 * REDIRECCIÓN AL NUEVO MÓDULO DE HOSPEDAJE (Usa POST para limpiar la URL)
 */
function hospedar_ocupar(action, numero, tipo, precio, habitacionID) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../hospedajes/hospedaje_nuevo.php';

    var params = {
        'numero': numero,
        'tipo': tipo,
        'precio': precio,
        'habitacionID': habitacionID
    };

    for (var key in params) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = params[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

/**
 * ACCIONES RÁPIDAS (Se mantienen relativas a habitaciones/ porque están ahí)
 */
function agregar_huesped(action, numero, tipo, habitacionID) {
    window.location.href = 'agregar.php?numero=' + numero + 
                           '&tipo=' + encodeURIComponent(tipo) + 
                           '&habitacionID=' + habitacionID + 
                           '&accion=' + action;
}

function cambiarEstado(habitacionID, nuevoEstado) {
    window.location.href = 'cambiar_estado.php?habitacionID=' + habitacionID + '&nuevoEstado=' + nuevoEstado;
}

function desocupar(habitacionID) {
    window.location.href = 'desocupar.php?habitacionID=' + habitacionID;
}

function desocupar1(habitacionID) {
    window.location.href = 'desocupar1.php?habitacionID=' + habitacionID;
}

function liberar(habitacionID) {
    window.location.href = 'liberar.php?habitacionID=' + habitacionID;
}

/**
 * GESTIÓN DE MODAL PERMANENCIA (AJAX apunta al módulo de Hospedajes)
 */
function mostrarModalPermanencia(habitacionID) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    fetch('../hospedajes/obtener_datos_hospedaje.php?habitacionID=' + habitacionID)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('No hay hospedaje activo en esta habitación.');
            } else {
                document.getElementById('permanencia-habitacion').value = data.numero;
                document.getElementById('permanencia-habitacionID').value = data.habitacionID;
                document.getElementById('permanencia-precio').value = data.precio;
                document.getElementById('permanencia-monto_pendiente').value = data.monto_total;
                document.getElementById('permanencia-formaPagoID').value = data.formaPagoID;
                document.getElementById('permanencia-hospedajeID').value = data.hospedajeID;

                const clientesContainer = document.getElementById('clientes-lista');
                clientesContainer.innerHTML = ''; 
                data.clientes.forEach(cliente => {
                    const clienteItem = document.createElement('li');
                    clienteItem.textContent = `${cliente.ci} - ${cliente.nombres} ${cliente.apellidos}`;
                    clienteItem.classList.add('list-group-item');
                    clientesContainer.appendChild(clienteItem);
                });

                var modalPermanencia = new bootstrap.Modal(document.getElementById('modal-permanencia'));
                modalPermanencia.show();
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * PAGO DE DEUDA (AJAX apunta al módulo de Hospedajes)
 */
function mostrarModalPagoDeuda(habitacionID) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    fetch('../hospedajes/obtener_datos_hospedaje.php?habitacionID=' + habitacionID)
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error al recuperar datos de deuda.');
        } else {
            document.getElementById('pago-deuda-habitacion').value = data.numero;
            document.getElementById('pago-deuda-habitacion-numero').value = data.numero;
            document.getElementById('pago-deuda-monto_total').value = data.monto_total;
            document.getElementById('pago-deuda-habitacionID').value = data.habitacionID;
            document.getElementById('pago-deuda-hospedajeID').value = data.hospedajeID;
            var modalPagoDeuda = new bootstrap.Modal(document.getElementById('modal-pago-deuda'));
            modalPagoDeuda.show();
        }
    })
    .catch(error => console.error('Error:', error));
}

/**
 * GESTIÓN DE RESERVAS
 */
function ocuparDesdeReserva(habitacionID) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    fetch('obtener_datos_reserva.php') // Este se mantiene en habitaciones por ahora
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('No se encontró reserva.');
            } else {
                window.location.href = `../hospedajes/hospedaje_reserva.php?habitacionID=${habitacionID}`;
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * ACTUALIZACIÓN AUTOMÁTICA DE ESTADOS (Polling)
 */
function actualizarEstadoHabitaciones() {
    fetch('obtener_estados_habitaciones.php') 
    .then(response => response.json())
    .then(data => {
        data.forEach(function(habitacion) {
            const btnHabitacion = document.getElementById('habitacion-' + habitacion.habitacionID);
            if (btnHabitacion) {
                let btnClass = 'btn-habitacion w-100 p-3 shadow-sm d-flex flex-column align-items-center justify-content-center';
                switch (habitacion.estado) {
                    case 'DISPONIBLE': btnClass += ' btn btn-success'; break;
                    case 'OCUPADA': btnClass += ' btn btn-primary'; break;
                    case 'DEUDA': btnClass += ' btn btn-danger'; break;
                    case 'LIMPIEZA': btnClass += ' btn btn-secondary'; break;
                    case 'RESERVADA': btnClass += ' btn btn-info'; break;
                    case 'MOMENTANEO': btnClass += ' btn btn-warning'; break;
                    default: btnClass += ' btn btn-dark';
                }
                btnHabitacion.className = btnClass;
            }
        });
    })
    .catch(error => console.error('Error al actualizar:', error));
}

setInterval(actualizarEstadoHabitaciones, 60000);

function momentaneo(numero, habitacionID) {
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    document.getElementById('momentaneo-habitacion').value = numero;
    document.getElementById('momentaneo-habitacionID').value = habitacionID;
    
    const modalmomentaneo = new bootstrap.Modal(document.getElementById('modal-momentaneo'));
    modalmomentaneo.show();
}

function mantenimiento(numero, habitacionID) {
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    document.getElementById('mantenimiento-habitacion').value = numero;
    document.getElementById('mantenimiento-habitacionID').value = habitacionID;
    
    const modalmantenimiento = new bootstrap.Modal(document.getElementById('modal-mantenimiento'));
    modalmantenimiento.show();
}

document.addEventListener('DOMContentLoaded', function() {
    // Guardar Momentaneo
    const btnGuardarMom = document.getElementById("guardar-momentaneo");
    if (btnGuardarMom) {
        btnGuardarMom.addEventListener("click", function() {
            document.getElementById("form-momentaneo").submit();
        });
    }

    // Guardar Mantenimiento
    const btnGuardarMant = document.getElementById('guardar-mantenimiento');
    if (btnGuardarMant) {
        btnGuardarMant.addEventListener('click', function() {
            document.getElementById('form-mantenimiento').submit();
        });
    }
});
