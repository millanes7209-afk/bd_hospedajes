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

function mostrarModalBano(tipo) {
    document.getElementById('bano-tipo-titulo').innerText = (tipo === 'INGRESO') ? 'INGRESO BAÑO' : 'EGRESO BAÑO';
    document.getElementById('bano-tipo-input').value = tipo;
    
    // Cambiar color del botón según el tipo
    const btn = document.getElementById('btn-guardar-bano');
    if (tipo === 'INGRESO') {
        btn.className = 'btn btn-primary font-weight-bold';
    } else {
        btn.className = 'btn btn-danger font-weight-bold';
    }

    var modalBano = new bootstrap.Modal(document.getElementById('modal-bano'));
    modalBano.show();
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
            <button type="button" class="btn btn-info" onclick="momentaneo('${numero}', '${habitacionID}', '${precio}')">SIN CARNET</button>
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
            <button type="button" class="btn btn-primary" onclick="mostrarModalPermanencia('${habitacionID}')">PAGAR Y OCUPAR</button>
            <button type="button" class="btn btn-secondary" onclick="mostrarModalPagoDeuda('${habitacionID}', '${precio}')">PAGAR Y DESOCUPAR</button>
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
                // FILTRADO POR EMPRESA: El PHP 'obtener_estados_habitaciones.php' ya filtra estrictamente por la sesión del usuario.
                
                // LÓGICA DE "MANTENER" EN VEZ DE "RECREAR":
                // Comprobamos si los datos clave han cambiado antes de tocar el DOM
                const estadoPrevio = btnHabitacion.getAttribute('data-estado-actual');
                const clientePrevio = btnHabitacion.getAttribute('data-cliente-actual');
                const montoPrevio = btnHabitacion.getAttribute('data-monto-actual');
                
                // Si nada ha cambiado, saltamos la actualización de este botón para no romper el hover/tooltip actual
                if (estadoPrevio === habitacion.estado && 
                    clientePrevio === (habitacion.cliente_activo || '') && 
                    montoPrevio === String(habitacion.precio_inteligente)) {
                    return; 
                }

                // Si llegamos aquí es porque algo cambió, procedemos a actualizar
                btnHabitacion.className = btnClass;
                btnHabitacion.setAttribute('data-estado-actual', habitacion.estado);
                btnHabitacion.setAttribute('data-cliente-actual', habitacion.cliente_activo || '');
                btnHabitacion.setAttribute('data-monto-actual', habitacion.precio_inteligente);
                
                // Actualizar el atributo onclick con el precio inteligente
                btnHabitacion.setAttribute('onclick', `handleHabitacionClick('${habitacion.estado}', '${habitacion.numero}', '${habitacion.tipo}', '${habitacion.precio_inteligente}', '${habitacion.habitacionID}')`);

                // RECONSTRUCCIÓN DEL CONTENIDO (Solo ocurre si hay cambios reales)
                let innerHTML = '';
                
                // 1. Cuerpo del botón
                if (habitacion.estado === 'DEUDA') {
                    innerHTML += `<span>DEUDA</span><strong>${habitacion.numero}</strong>`;
                } else {
                    innerHTML += `<span>${habitacion.estado}</span><strong>${habitacion.numero}</strong>`;
                }

                // 2. Tooltips y Badges (Fichas Flotantes)
                if ((habitacion.estado === 'OCUPADA' || habitacion.estado === 'DEUDA') && habitacion.cliente_activo) {
                    let d = new Date(habitacion.checkout_activo);
                    let formattedDate = ("0" + d.getDate()).slice(-2) + "/" + ("0" + (d.getMonth() + 1)).slice(-2) + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                    
                    let badgeColor = (habitacion.estado === 'DEUDA') ? 'background:#dc3545; color:#fff; border-color:#dc3545;' : '';
                    let badgeLabel = (habitacion.estado === 'DEUDA') ? 'DEUDA Bs.' : 'Bs.';
                    
                    innerHTML += `
                        <span class="badge-precio" style="${badgeColor}">${badgeLabel} ${parseInt(habitacion.precio_inteligente)}</span>
                        <div class="habitacion-info-tooltip">
                            <div class="tooltip-header" ${habitacion.estado === 'DEUDA' ? 'style="background-color: #dc3545;"' : ''}>
                                <i class="fas ${habitacion.estado === 'DEUDA' ? 'fa-exclamation-triangle' : 'fa-user-circle'}"></i>
                                ${habitacion.estado === 'DEUDA' ? 'DEUDA VENCIDA' : 'DETALLE OCUPACIÓN'}
                            </div>
                            <div class="tooltip-body">
                                <p><strong>CLIENTE:</strong><br>${habitacion.cliente_activo.toUpperCase()}</p>
                                <p><strong>SALIDA:</strong> ${formattedDate}</p>
                                <p><strong>TIPO:</strong> ${habitacion.tipo}</p>
                            </div>
                        </div>
                    `;
                } else if (habitacion.estado === 'DISPONIBLE') {
                    innerHTML += `<span class="badge-precio">Bs. ${parseInt(habitacion.precio_base)}</span>
                        <div style="position: absolute; top: 4px; left: 4px; display: flex; flex-direction: column; gap: 2px; align-items: flex-start;">
                            ${habitacion.tv == 1 ? '<span style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">TV</span>' : ''}
                            ${habitacion.bano == 1 ? '<span style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">BAÑO</span>' : ''}
                            ${habitacion.ventilador == 1 ? '<span style="font-size: 7px; background: rgba(0,0,0,0.6); color: white; padding: 1px 2px; border-radius: 2px; line-height: 1;">VENT</span>' : ''}
                        </div>`;
                } else {
                    innerHTML += `<span class="estado-label">${habitacion.estado === 'MANTENIMIENTO' ? 'MANT.' : habitacion.estado}</span>`;
                    if (habitacion.estado === 'MANTENIMIENTO' && habitacion.habitacion_descripcion) {
                        innerHTML += `
                            <div class="habitacion-info-tooltip">
                                <div class="tooltip-header" style="background-color: #343a40; color: white; border-color: #555;">
                                    <i class="fas fa-tools"></i> MANTENIMIENTO
                                </div>
                                <div class="tooltip-body">
                                    <p><strong>DESCRIPCIÓN:</strong><br>${habitacion.habitacion_descripcion}</p>
                                </div>
                            </div>
                        `;
                    }
                }

                btnHabitacion.innerHTML = innerHTML;
            }
        });
    })
    .catch(error => console.error('Error al actualizar:', error));
}

setInterval(actualizarEstadoHabitaciones, 60000);

/**
 * PAGO DE DEUDA
 */
function mostrarModalPagoDeuda(habitacionID, monto) {
    var modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    // Usar el monto ya calculado, sin necesidad de fetch
    document.getElementById('pago-deuda-monto_total').value = parseFloat(monto) || 0;
    document.getElementById('pago-deuda-habitacionID').value = habitacionID;

    // Buscar el hospedajeID y número de la habitación desde los datos ya cargados
    fetch('obtener_datos_hospedaje.php?habitacionID=' + habitacionID + '&auth=habitaciones.php')
    .then(response => response.json())
    .then(data => {
        if (!data.error) {
            document.getElementById('pago-deuda-habitacion').innerText = data.numero;
            document.getElementById('pago-deuda-habitacion-numero').value = data.numero;
            document.getElementById('pago-deuda-hospedajeID').value = data.hospedajeID;
        }
    })
    .catch(error => console.error('Error:', error));

    var modalPagoDeuda = new bootstrap.Modal(document.getElementById('modal-pago-deuda'));
    modalPagoDeuda.show();
}

/**
 * REGISTROS MOMENTÁNEOS Y MANTENIMIENTO
 */
function momentaneo(numero, habitacionID, precio) {
    const modalOpciones = bootstrap.Modal.getInstance(document.getElementById('menu-opciones'));
    if (modalOpciones) modalOpciones.hide();

    document.getElementById('momentaneo-habitacion').value = numero;
    document.getElementById('momentaneo-habitacionID').value = habitacionID;

    // Limpiar campo monto y pagos al abrir
    const campoMonto = document.getElementById('mom-monto_total');
    if (campoMonto) campoMonto.value = '';

    const contenedor = document.getElementById('contenedorPagosMom');
    if (contenedor) {
        contenedor.innerHTML = '';
        // Agregar la primera fila de pago automáticamente
        agregarFilaPagoMom();
    }
    actualizarResumenMom();

    const modalmomentaneo = new bootstrap.Modal(document.getElementById('modal-momentaneo'));
    modalmomentaneo.show();
}

// ---- PAGOS MÚLTIPLES MOMENTÁNEO ----
function agregarFilaPagoMom() {
    const contenedor = document.getElementById('contenedorPagosMom');
    const index = contenedor.children.length;
    const selectTemplate = document.getElementById('templateFormaPagoMom').innerHTML;

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center fila-pago-mom';
    div.innerHTML = `
        <div class="col-md-7">
            <select class="form-control form-control-sm" name="pagos[${index}][formaPagoID]" required onchange="actualizarResumenMom()">
                ${selectTemplate}
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control form-control-sm input-monto-mom"
                   name="pagos[${index}][monto]" placeholder="Monto" step="0.5" required
                   oninput="actualizarResumenMom()">
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarFilaPagoMom(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    contenedor.appendChild(div);
    actualizarResumenMom();
}

function eliminarFilaPagoMom(btn) {
    const contenedor = document.getElementById('contenedorPagosMom');
    if (contenedor.children.length > 1) {
        btn.closest('.fila-pago-mom').remove();
        actualizarResumenMom();
    }
}

function actualizarResumenMom() {
    const total = parseFloat(document.getElementById('mom-monto_total')?.value) || 0;
    let pagado = 0;
    document.querySelectorAll('.input-monto-mom').forEach(i => { pagado += parseFloat(i.value) || 0; });
    const saldo = total - pagado;

    const elPagado = document.getElementById('momDisplayPagado');
    const elSaldo  = document.getElementById('momDisplaySaldo');
    if (elPagado) elPagado.innerText = pagado.toFixed(2);
    if (elSaldo) {
        elSaldo.innerText = saldo.toFixed(2);
        elSaldo.className = (Math.abs(saldo) < 0.01) ? 'text-success fw-bold' : 'text-danger fw-bold fs-5';
    }
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
