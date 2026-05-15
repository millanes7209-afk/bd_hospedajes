<!-- Modal de Confirmación de Cierre de Caja -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1" role="dialog" aria-labelledby="modalCerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCerrarCajaLabel">
                    Confirmar Cierre de Caja
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="saldosContainer">
                    Cargando saldos...
                </div>
                
                <div class="border-top pt-2 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-muted">Total Habitaciones:</span>
                        <span class="fw-bold">Bs. <span id="totalHabitacionesCierre">0.00</span></span>
                    </div>
                    
                    <!-- SECCIÓN DE BAÑOS -->
                    <div id="banoSummaryContainer" class="d-flex justify-content-between align-items-center mb-1 text-info" style="display:none !important;">
                        <span class="fw-bold"><i class="fas fa-restroom"></i> Total Baños:</span>
                        <span class="fw-bold">Bs. <span id="totalBanoCierre">0.00</span></span>
                    </div>

                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <h4 class="mb-0 fw-bold text-primary">TOTAL GENERAL:</h4>
                        <h4 class="mb-0 fw-bold text-primary">Bs. <span id="totalGlobalCierre">0.00</span></h4>
                    </div>
                </div>
                
                <p class="mt-4 text-center">¿Está seguro de cerrar la caja?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <form id="formCerrarCaja" method="post" action="../../procesar_caja.php" style="display: inline;">
                    <input type="hidden" name="accion" value="cerrar">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        Confirmar Cierre
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let totalHab = 0;
let totalBan = 0;

// Función para mostrar datos cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    const modalCerrarCaja = document.getElementById('modalCerrarCaja');
    
    if (modalCerrarCaja) {
        // Forzar carga de datos cuando se carga la página
        setTimeout(() => {
            mostrarDatosCaja();
            cargarResumenBano();
        }, 800);
    }
});

function actualizarTotalGlobal() {
    const totalGlobal = totalHab + totalBan;
    document.getElementById('totalGlobalCierre').innerText = totalGlobal.toFixed(2);
}

function cargarResumenBano() {
    // Detectar si estamos en la raíz o en una subcarpeta para ajustar la ruta
    let rutaResumen = 'privada/habitacioness/ajax_bano_resumen.php';
    if (window.location.pathname.includes('/privada/')) {
        rutaResumen = 'ajax_bano_resumen.php';
    }

    fetch(rutaResumen)
    .then(r => r.json())
    .then(res => {
        if (res.status === 'ok') {
            totalBan = res.total;
            document.getElementById('totalBanoCierre').innerText = totalBan.toFixed(2);
            if (totalBan !== 0) {
                document.getElementById('banoSummaryContainer').style.setProperty('display', 'flex', 'important');
            }
            actualizarTotalGlobal();
        }
    });
}

// Función para mostrar datos de la caja (leyendo del DOM)
function mostrarDatosCaja() {
    const saldosContainer = document.getElementById('saldosContainer');
    const totalHabSpan = document.getElementById('totalHabitacionesCierre');
    
    // Obtener los datos del DOM que ya están mostrados en la cabecera
    const saldoAcumuladoDiv = document.getElementById('saldo-acumulado');
    const saldoText = saldoAcumuladoDiv ? saldoAcumuladoDiv.textContent : '';
    
    // Parsear los saldos
    const saldos = {};
    totalHab = 0;
    const regex = /\(([^)]+)\):\s*Bs\.\s*([\d.,]+)/g;
    let match;
    
    while ((match = regex.exec(saldoText)) !== null) {
        const formaPago = match[1];
        const monto = parseFloat(match[2].replace(',', '.'));
        saldos[formaPago] = monto;
        totalHab += monto;
    }
    
    // Limpiar contenedor
    saldosContainer.innerHTML = '';
    
    // Crear tabla simple
    let tablaHTML = `<table class="table table-sm mb-0"><tbody>`;
    let haySaldos = false;
    for (const [formaPago, monto] of Object.entries(saldos)) {
        if (monto !== 0) {
            haySaldos = true;
            tablaHTML += `
                <tr>
                    <td class="text-muted">${formaPago}</td>
                    <td style="text-align: right;" class="fw-bold">Bs. ${monto.toFixed(2)}</td>
                </tr>`;
        }
    }
    tablaHTML += `</tbody></table>`;
    
    saldosContainer.innerHTML = (haySaldos) ? tablaHTML : '<p class="text-center text-muted mb-0">No se detectaron ingresos en habitaciones.</p>';
    totalHabSpan.textContent = totalHab.toFixed(2);
    actualizarTotalGlobal();
}
</script>
