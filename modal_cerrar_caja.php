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
                
                <hr>
                <p><strong>Total: <span id="totalGeneral">Bs. 0.00</span></strong></p>
                
                <p>¿Está seguro de cerrar la caja?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    Cancelar
                </button>
                <form id="formCerrarCaja" method="post" action="../../procesar_caja.php" style="display: inline;">
                    <input type="hidden" name="accion" value="cerrar">
                    <button type="submit" class="btn btn-primary">
                        Confirmar Cierre
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Función para mostrar datos cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    const modalCerrarCaja = document.getElementById('modalCerrarCaja');
    
    if (modalCerrarCaja) {
        // Forzar carga de datos cuando se carga la página
        setTimeout(() => {
            mostrarDatosCaja();
        }, 800);
    }
});

// Función para mostrar datos de la caja (leyendo del DOM)
function mostrarDatosCaja() {
    const saldosContainer = document.getElementById('saldosContainer');
    const totalGeneral = document.getElementById('totalGeneral');
    
    // Obtener los datos del DOM que ya están mostrados en la cabecera
    const saldoAcumuladoDiv = document.getElementById('saldo-acumulado');
    const saldoText = saldoAcumuladoDiv ? saldoAcumuladoDiv.textContent : '';
    
    // Parsear los saldos
    const saldos = {};
    let total = 0;
    const regex = /\(([^)]+)\):\s*Bs\.\s*([\d.,]+)/g;
    let match;
    
    while ((match = regex.exec(saldoText)) !== null) {
        const formaPago = match[1];
        const monto = parseFloat(match[2].replace(',', '.'));
        saldos[formaPago] = monto;
        total += monto;
    }
    
    // Limpiar contenedor
    saldosContainer.innerHTML = '';
    
    // Crear tabla simple
    let tablaHTML = `<table class="table"><tbody>`;
    for (const [formaPago, monto] of Object.entries(saldos)) {
        if (monto > 0) {
            tablaHTML += `
                <tr>
                    <td>${formaPago}</td>
                    <td style="text-align: right;">Bs. ${monto.toFixed(2)}</td>
                </tr>`;
        }
    }
    tablaHTML += `</tbody></table>`;
    
    saldosContainer.innerHTML = (total > 0) ? tablaHTML : '<p class="text-center text-muted">No se detectaron movimientos en este turno.</p>';
    totalGeneral.textContent = total.toFixed(2);
}
</script>
