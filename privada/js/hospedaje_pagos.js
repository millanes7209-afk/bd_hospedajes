/**
 * GESTIÓN DE PAGOS HÍBRIDOS (MÚLTIPLES FORMAS DE PAGO)
 * Ubicación: privada/js/hospedaje_pagos.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar la primera fila de pago si está vacío
    actualizarResumenPagos();
});

function agregarFilaPago() {
    const contenedor = document.getElementById('contenedorPagos');
    const index = contenedor.children.length;

    // Obtener las opciones de formas de pago (clonamos el select oculto que crearemos en PHP)
    const selectTemplate = document.getElementById('templateFormaPago').innerHTML;

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 align-items-center fila-pago';
    div.innerHTML = `
        <div class="col-md-7">
            <select class="form-control form-control-sm select-fp" name="pagos[${index}][formaPagoID]" required onchange="actualizarResumenPagos()">
                ${selectTemplate}
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" class="form-control form-control-sm input-monto-pago" name="pagos[${index}][monto]" 
                   placeholder="Monto" step="0.5" required oninput="actualizarResumenPagos()">
        </div>
        <div class="col-md-1 text-end">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="eliminarFilaPago(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    contenedor.appendChild(div);
    actualizarResumenPagos();
}

function eliminarFilaPago(btn) {
    const contenedor = document.getElementById('contenedorPagos');
    if (contenedor.children.length > 1) {
        btn.closest('.fila-pago').remove();
        actualizarResumenPagos();
    }
}

function actualizarResumenPagos() {
    const totalHospedaje = parseFloat(document.getElementById('monto_total').value) || 0;
    const inputsMonto = document.querySelectorAll('.input-monto-pago');
    
    let totalPagado = 0;
    inputsMonto.forEach(input => {
        totalPagado += parseFloat(input.value) || 0;
    });

    const saldoPendiente = totalHospedaje - totalPagado;
    
    // Actualizar UI
    const displayPagado = document.getElementById('displayTotalPagado');
    const displaySaldo = document.getElementById('displaySaldoPendiente');
    const btnRegistrar = document.querySelector('button[type="submit"]');

    if (displayPagado) displayPagado.innerText = totalPagado.toFixed(2);
    if (displaySaldo) {
        displaySaldo.innerText = saldoPendiente.toFixed(2);
        
        // Visualización de alertas (No bloquea botones)
        if (saldoPendiente === 0) {
            displaySaldo.className = 'text-success fw-bold';
        } else {
            displaySaldo.className = 'text-danger fw-bold fs-5';
        }
    }
}

// Escuchar cambios en el precio total para recalcular saldo
document.getElementById('monto_total')?.addEventListener('input', actualizarResumenPagos);
