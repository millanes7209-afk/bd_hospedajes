/**
 * LÓGICA DE NEGOCIO PARA EL MAPA INTERACTIVO DE HABITACIONES
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
    
    // Asignar el número de habitación al badge cabecera
    const spanBadge = document.getElementById('modal-habitacion-badge');
    if (spanBadge) spanBadge.innerText = numero;

    if (estado === 'DISPONIBLE') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary" onclick="hospedar_ocupar('hospedar', '${numero}', '${tipo}', '${precio}', '${habitacionID}')">HOSPEDAR</button>
            <button type="button" class="btn btn-info" onclick="momentaneo('${numero}', '${habitacionID}')">SIN CARNET</button>
        `;
        modal.show();
    } else if (estado === 'OCUPADA') {
        modalFooter.innerHTML = `
            <button type="button" class="btn btn-primary w-100" onclick="mostrarModalPermanencia('${habitacionID}')"><i class="fas fa-calendar-plus"></i> PERMANENCIA</button>
            <button type="button" class="btn btn-success w-100 font-weight-bold" onclick="mostrarModalCambioHabitacion('${habitacionID}')"><i class="fas fa-exchange-alt"></i> CAMBIAR HABITACIÓN</button>
            <button type="button" class="btn btn-info w-100" onclick="agregar_huesped('aumentar', '${numero}', '${tipo}', '${habitacionID}')"><i class="fas fa-user-plus"></i> AGREGAR HUESPED</button>
            <button type="button" class="btn btn-secondary w-100" onclick="desocupar('${habitacionID}')"><i class="fas fa-sign-out-alt"></i> DESOCUPAR</button>
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
            <button type="button" class="btn btn-primary" onclick="mostrarModalPermanencia('${habitacionID}', '${precio}')">PAGAR Y OCUPAR</button>
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
        'habitacionID': habitacionID,
        'auth': 'habitaciones.php'
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

function agregar_huesped(action, numero, tipo, habitacionID) {
    // Redirección profesional usando POST enviando hospedajeID real
    fetch('obtener_datos_hospedaje.php?habitacionID=' + habitacionID + '&auth=habitaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('No hay un hospedaje activo para añadir huéspedes.');
            } else {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '../hospedajes/hospedaje_agregar_huesped.php';

                var params = {
                    'numero': numero,
                    'tipo': tipo,
                    'habitacionID': habitacionID,
                    'hospedajeID': data.hospedajeID,
                    'auth': 'habitaciones.php'
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
        });
}

function cambiarEstado(habitacionID, nuevoEstado) {
    window.location.href = 'cambiar_estado.php?habitacionID=' + habitacionID + '&nuevoEstado=' + nuevoEstado + '&auth=habitaciones.php';
}

function desocupar(habitacionID) {
    window.location.href = 'desocupar.php?habitacionID=' + habitacionID + '&auth=habitaciones.php';
}

function desocupar1(habitacionID) {
    window.location.href = 'desocupar1.php?habitacionID=' + habitacionID + '&auth=habitaciones.php';
}

function liberar(habitacionID) {
    window.location.href = 'liberar.php?habitacionID=' + habitacionID + '&auth=habitaciones.php';
}

/**
 * GESTIÓN DE PERMANENCIA - Redirección a pantalla completa
 */
function mostrarModalPermanencia(habitacionID, monto_deuda = 0) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    // Crear formulario dinámico para saltar a la pantalla de permanencia
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '../hospedajes/hospedaje_permanencia.php';

    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'hospedajeID';
    
    // Obtener el hospedajeID actual vía API antes de saltar
    fetch('obtener_datos_hospedaje.php?habitacionID=' + habitacionID + '&auth=habitaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('No hay un hospedaje activo para realizar la permanencia.');
            } else {
                input.value = data.hospedajeID;
                form.appendChild(input);

                var authInput = document.createElement('input');
                authInput.type = 'hidden';
                authInput.name = 'auth';
                authInput.value = 'habitaciones.php';
                form.appendChild(authInput);

                if (monto_deuda > 0) {
                    var montoInput = document.createElement('input');
                    montoInput.type = 'hidden';
                    montoInput.name = 'monto_deuda';
                    montoInput.value = monto_deuda;
                    form.appendChild(montoInput);
                }

                document.body.appendChild(form);
                form.submit();
            }
        });
}

/**
 * GESTIÓN DE CAMBIO DE HABITACIÓN
 */
function mostrarModalCambioHabitacion(habitacionID) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    const vStamp = new Date().getTime();
    fetch('obtener_datos_hospedaje.php?habitacionID=' + habitacionID + '&auth=habitaciones.php&v=' + vStamp)
        .then(response => response.text())
        .then(text => {
            try {
                return JSON.parse(text);
            } catch(e) {
                alert("Fallo crítico en obtener_datos_hospedaje.php:\n\n" + text.substring(0, 300));
                throw new Error("Bad JSON Hospedaje");
            }
        })
        .then(data => {
            if (data.error) {
                alert('Error al procesar los datos en el servidor: ' + data.error);
            } else {
                document.getElementById('cambio-hospedajeID').value = data.hospedajeID;
                document.getElementById('cambio-habitacionID-actual').value = data.habitacionID;
                document.getElementById('cambio-texto-actual').innerText = data.numero;

                // Llenar combo disponibles evitando cache
                fetch('get_habitaciones_disponibles.php?auth=habitaciones.php&v=' + vStamp)
                    .then(res => res.text())
                    .then(text2 => {
                        try {
                            return JSON.parse(text2);
                        } catch(e) {
                            alert("Fallo crítico en get_habitaciones_disponibles.php:\n\n" + text2.substring(0, 300));
                            throw new Error("Bad JSON Disponibles");
                        }
                    })
                    .then(disponibles => {
                        if (disponibles.error) {
                            alert("Error al cargar disponibles: " + disponibles.error);
                            return;
                        }
                        const grid = document.getElementById('grid-habitaciones-disponibles');
                        const hiddenInput = document.getElementById('cambio-nueva-habitacion');
                        const btnConfirmar = document.getElementById('btn-ejecutar-cambio');

                        grid.innerHTML = '';
                        hiddenInput.value = '';
                        btnConfirmar.disabled = true;

                        if (disponibles.length === 0) {
                            grid.innerHTML = '<p class="text-muted">No hay habitaciones disponibles en este momento.</p>';
                        } else {
                            disponibles.forEach(hab => {
                                const btn = document.createElement('button');
                                btn.type = 'button';
                                btn.className = 'btn btn-success';
                                btn.style.cssText = 'min-width:70px; font-size:1.1em; font-weight:bold; padding: 10px 14px;';
                                btn.innerText = hab.numero;
                                btn.title = hab.nombre + ' - Bs. ' + hab.precio;
                                btn.onclick = function() {
                                    // Deseleccionar anterior
                                    grid.querySelectorAll('.btn').forEach(b => {
                                        b.classList.remove('btn-dark');
                                        b.classList.add('btn-success');
                                    });
                                    // Marcar seleccionado
                                    btn.classList.remove('btn-success');
                                    btn.classList.add('btn-dark');
                                    hiddenInput.value = hab.habitacionID;
                                    btnConfirmar.disabled = false;
                                };
                                grid.appendChild(btn);
                            });
                        }
                        var modal = new bootstrap.Modal(document.getElementById('modal-cambio-habitacion'));
                        modal.show();
                    });
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

    fetch('obtener_datos_reserva.php?habitacionID=' + habitacionID + '&auth=habitaciones.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert('Error al obtener los datos de la reserva.');
            } else {
                const url = `hospedaje_reserva.php?numero=${data.numero}&tipo=${data.tipo}&precio=${data.precio}&habitacionID=${data.habitacionID}&reservaID=${data.reservaID}&monto_total=${data.monto_total}&monto_pagado=${data.monto_pagado}&monto_pendiente=${data.monto_pendiente}&clienteID=${data.clienteID}&auth=habitaciones.php`;
                window.location.href = url;
            }
        })
        .catch(error => console.error('Error:', error));
}

/**
 * ACTUALIZACIÓN AUTOMÁTICA DE ESTADOS (Polling)
 */
function actualizarEstadoHabitaciones() {
    fetch('obtener_estados_habitaciones.php?auth=habitaciones.php')
    .then(response => response.json())
    .then(data => {
        data.forEach(function(habitacion) {
            const btnHabitacion = document.getElementById('habitacion-' + habitacion.habitacionID);
            if (btnHabitacion) {
                let btnClass = 'btn-habitacion';
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

                if (habitacion.estado === 'OCUPADA' && habitacion.cliente_activo) {
                    // Renderizado Smart (Ocupada)
                    let d = new Date(habitacion.checkout_activo);
                    let formattedDate = ("0" + d.getDate()).slice(-2) + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                    
                    btnHabitacion.innerHTML = `
                        <div style="font-size: 11px; font-weight: bold; line-height: 1.1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 100%; margin-bottom: 2px;" title="${habitacion.cliente_activo}">
                            <i class="fas fa-user mr-1"></i> ${habitacion.cliente_activo.toUpperCase()}
                        </div>
                        <strong>${habitacion.numero}</strong>
                        <div style="font-size: 10px; margin-top: 2px; opacity: 0.9;">
                            <i class="fas fa-sign-out-alt mr-1"></i> ${formattedDate}
                        </div>
                    `;
                } else {
                    // Renderizado Estándar
                    btnHabitacion.innerHTML = `<span>${habitacion.estado}</span><strong>${habitacion.numero}</strong>`;
                }
            }
        });
    })
    .catch(error => console.error('Error al actualizar:', error));
}

setInterval(actualizarEstadoHabitaciones, 60000);

/**
 * PAGO DE DEUDA
 */
function mostrarModalPagoDeuda(habitacionID) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    fetch('obtener_datos_hospedaje.php?habitacionID=' + habitacionID + '&auth=habitaciones.php')
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error.');
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
 * REGISTROS MOMENTÁNEOS Y MANTENIMIENTO
 */
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

/**
 * EVENT LISTENERS AL CARGAR
 */
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

    // Modal Notificación Custom (miModal)
    const cerrarModalNotif = document.querySelector("#miModal .cerrar");
    if (cerrarModalNotif) {
        cerrarModalNotif.onclick = function() {
            document.getElementById("miModal").style.display = "none";
        }
    }

    // Notificaciones iniciales
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '../../ejecutar_notificaciones.php', true); 
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            // Notificaciones ejecutadas exitosamente
        }
    };
    xhr.send();
});
