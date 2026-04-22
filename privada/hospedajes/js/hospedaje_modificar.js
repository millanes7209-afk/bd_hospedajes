let montoOriginal = 0;
let pagosOriginalesJSON = "";

document.addEventListener("DOMContentLoaded", function () {
    cargarDatosHospedaje();

    // Validar envío del formulario e interceptar para auditoría
    const form = document.getElementById('formModificarHospedaje');
    form.addEventListener('submit', function (e) {
        // 1. Validar Saldo 0
        const saldo = parseFloat(document.getElementById('saldoValor').innerText);
        if (Math.abs(saldo) > 0.01) {
            e.preventDefault();
            alert("Error: El Saldo debe ser 0.00 para poder guardar los cambios.");
            return;
        }

        // 2. Validar Auditoría de Precio o Formas de Pago
        const montoActual = parseFloat(document.getElementById('monto_total').value) || 0;
        const pagosActualesJSON = obtenerJSONPagos();
        const motivoOculto = document.getElementById('motivo_auditoria').value;

        const cambioMonto = Math.abs(montoActual - montoOriginal) > 0.01;
        const cambioDistribucion = pagosActualesJSON !== pagosOriginalesJSON;

        // Si algo financiero cambió y aún no se ha capturado el motivo
        if ((cambioMonto || cambioDistribucion) && motivoOculto.trim() === "") {
            e.preventDefault();
            
            // Personalizar el mensaje del modal segun el tipo de cambio
            const msgBody = document.querySelector('#modalMotivoPrecio .modal-body p');
            if (cambioMonto) {
                msgBody.innerHTML = "Se ha detectado una modificación en el <b>monto total</b> del hospedaje.";
            } else {
                msgBody.innerHTML = "Se ha detectado una modificación en la <b>distribución de las formas de pago</b>.";
            }

            const modalAudit = new bootstrap.Modal(document.getElementById('modalMotivoPrecio'));
            modalAudit.show();
        }
    });

    // Manejar confirmación del modal de auditoría
    document.getElementById('btnConfirmarAudit').addEventListener('click', function() {
        const motivo = document.getElementById('txtMotivoPrecio').value;
        const errorDiv = document.getElementById('errorAudit');

        if (motivo.trim().length < 5) {
            errorDiv.style.display = 'block';
            return;
        }

        errorDiv.style.display = 'none';
        document.getElementById('motivo_auditoria').value = motivo;
        // Re-enviar el formulario
        document.getElementById('formModificarHospedaje').submit();
    });
});

function obtenerJSONPagos() {
    const pagos = [];
    document.querySelectorAll('.select-pago').forEach((select, index) => {
        const montoInput = document.querySelectorAll('.monto-pago')[index];
        pagos.push({
            id: select.name.match(/\[(\d+)\]/)[1], // Indice del array original
            forma: select.value,
            monto: parseFloat(montoInput.value).toFixed(2)
        });
    });
    return JSON.stringify(pagos);
}

function cargarDatosHospedaje() {
    if (!window.hospedajeID) {
        console.error("No se encontró hospedajeID.");
        return;
    }

    fetch(`api_obtener_hospedaje.php?hospedajeID=${window.hospedajeID}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("Error al cargar datos: " + data.error);
                return;
            }
            montoOriginal = parseFloat(data.total_pagado); 
            montoOriginal = parseFloat(data.total_pagado); 
            window.esPropietario = data.es_propietario;
            
            if (!window.esPropietario) {
                // Bloqueo total
                document.getElementById('displayHabitacion').insertAdjacentHTML('beforebegin', '<div class="alert alert-danger fw-bold text-center mt-2"><i class="fas fa-ban"></i> ACCESO DENEGADO: Solo la caja y usuario original pueden modificar este hospedaje.</div>');
            } else {
                // Es propietario, solo un recordatorio
                document.getElementById('displayHabitacion').insertAdjacentHTML('beforebegin', '<div class="alert alert-info py-1 small fw-bold text-center mt-2">EDICIÓN RESTRINGIDA: Solo puede modificar estado y fechas. El aspecto financiero queda sellado.</div>');
            }

            renderizarFormulario(data);
            
            // Capturar estado inicial de los pagos DESPUÉS de renderizar
            setTimeout(() => {
                pagosOriginalesJSON = obtenerJSONPagos();
            }, 100);
        })
        .catch(error => {
            console.error("Error en peticion AJAX:", error);
        });
}

function renderizarFormulario(data) {
    const h = data.hospedaje;

    if (document.getElementById('displayHabitacion')) {
        document.getElementById('displayHabitacion').innerHTML = `HABITACIÓN: <strong>${h.habitacion_numero}</strong>`;
    }

    document.getElementById('estado').value = h.estado;
    if (h.checkout) {
        document.getElementById('checkout').value = h.checkout.replace(" ", "T").substring(0, 16);
    }
    
    // Si no es propietario, bloquear hasta los básicos
    if (!window.esPropietario) {
        document.getElementById('estado').disabled = true;
        document.getElementById('checkout').readOnly = true;
        document.getElementById('descripcion').readOnly = true;
        document.getElementById('btnGuardar').style.display = 'none';
    }

    const inputMonto = document.getElementById('monto_total');
    inputMonto.value = parseFloat(data.total_pagado).toFixed(2);
    // FINANCIERO SIEMPRE BLOQUEADO PARA TODOS
    inputMonto.readOnly = true;
    inputMonto.classList.add('bg-light');

    const contenedorClientes = document.getElementById('contenedorClientes');
    if (data.clientes && data.clientes.length > 0) {
        let htmlClientes = '<ul class="list-unstyled mb-0">';
        data.clientes.forEach(nombre => {
            htmlClientes += `<li class="py-1 border-bottom text-uppercase"><i class="fas fa-user-circle me-2"></i>${nombre}</li>`;
        });
        htmlClientes += '</ul>';
        contenedorClientes.innerHTML = htmlClientes;
    } else {
        contenedorClientes.innerHTML = '<p class="text-muted">Sin huéspedes registrados.</p>';
    }

    renderizarPagosEditables(data.movimientos);
    document.getElementById('descripcion').value = h.observaciones || '';
    recalcularTotal();
}

function renderizarPagosEditables(movimientos) {
    const contenedorPagos = document.getElementById('contenedorPagos');
    const template = document.getElementById('templateFormaPago').innerHTML;

    if (movimientos && movimientos.length > 0) {
        contenedorPagos.innerHTML = ''; 

        movimientos.forEach((mov, index) => {
            const divRow = document.createElement('div');
            divRow.className = 'row g-2 mb-2 align-items-center border-bottom pb-2';

            divRow.innerHTML = `
                <div class="col-md-7">
                    <select class="form-control form-control-sm select-pago" name="pagos[${index}][formaPagoID]" tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
                        ${template}
                    </select>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Bs</span>
                        <input type="number" class="form-control monto-pago" name="pagos[${index}][monto]" 
                               value="${parseFloat(mov.monto).toFixed(2)}" step="0.5" readonly class="bg-light">
                    </div>
                </div>
                <input type="hidden" name="pagos[${index}][movimientoID]" value="${mov.movimientoID}">
            `;

            contenedorPagos.appendChild(divRow);

            const select = divRow.querySelector('.select-pago');
            select.value = mov.formapagoID;
        });
    } else {
        contenedorPagos.innerHTML = '<p class="text-muted italic">No se encontraron movimientos financieros.</p>';
    }
}

function recalcularTotal() {
    const montoTotal = parseFloat(document.getElementById('monto_total').value) || 0;
    let sumaPagos = 0;

    document.querySelectorAll('.monto-pago').forEach(input => {
        sumaPagos += parseFloat(input.value) || 0;
    });

    const saldo = montoTotal - sumaPagos;
    const saldoDisplay = document.getElementById('saldoValor');
    const saldoContenedor = document.getElementById('contenedorSaldo');
    const alertaSaldo = document.getElementById('alertaSaldo');

    saldoDisplay.innerText = saldo.toFixed(2);

    if (Math.abs(saldo) < 0.01) {
        saldoContenedor.style.backgroundColor = '#d4edda'; 
        saldoContenedor.style.color = '#000';
        if (alertaSaldo) alertaSaldo.style.display = 'none';
        document.getElementById('btnGuardar').disabled = false;
    } else {
        saldoContenedor.style.backgroundColor = '#f8d7da'; 
        saldoContenedor.style.color = '#000';
        if (alertaSaldo) {
            alertaSaldo.innerHTML = "<i class='fas fa-exclamation-triangle'></i> El saldo debe ser exactamente 0.00";
            alertaSaldo.style.display = 'block';
        }
        document.getElementById('btnGuardar').disabled = true;
    }
}
