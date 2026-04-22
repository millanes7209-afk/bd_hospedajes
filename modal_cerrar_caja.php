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
                <div class="alert alert-info">
                    Caja abierta... <span id="fechaApertura"></span>
                </div>
                
                <div id="saldosContainer">
                    Cargando saldos...
                </div>
                
                <hr>
                <p><strong>Total: <span id="totalGeneral">Bs. 0.00</span></strong></p>
                
                <p>¿Está seguro de cerrar la caja?</p>

                <!-- DEPURADOR DE ENVÍO -->
                <div style="background: #f8f9fa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; font-size: 11px; font-family: monospace; margin-top: 20px;">
                    <b style="color: #d9534f; display: block; margin-bottom: 5px;">[DEBUG] DATOS A ENVIAR:</b>
                    Ruta: <span style="color: blue;">../../procesar_caja.php</span><br>
                    Acción: <span style="color: green;">cerrar</span><br>
                    CajaID: <span style="color: #611; font-weight: bold;"><?php echo $_SESSION['caja_abierta_id'] ?? 'N/A'; ?></span><br>
                    UsuarioID (Sesión): <span style="color: #611; font-weight: bold;"><?php echo $_SESSION['sesion_id_usuario'] ?? 'N/A'; ?></span><br>
                    EmpresaID: <span style="color: #611; font-weight: bold;"><?php echo $_SESSION['empresaID'] ?? 'N/A'; ?></span><br>
                    
                    <div style="margin-top: 5px; border-top: 1px dashed #ccc; padding-top: 5px;">
                        <b>Desglose de Saldos:</b><br>
                        <?php 
                        if (isset($saldos_forma_pago)) {
                            foreach ($saldos_forma_pago as $tipo => $monto) {
                                if ($monto > 0) {
                                    echo "• " . htmlspecialchars($tipo) . ": Bs. " . number_format($monto, 2) . "<br>";
                                }
                            }
                        } else {
                            echo "<span style='color:red;'>No se detectaron saldos</span>";
                        }
                        ?>
                    </div>
                </div>
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
        }, 1000);
    }
});

// Función para mostrar datos de la caja (leyendo del DOM)
function mostrarDatosCaja() {
    const saldosContainer = document.getElementById('saldosContainer');
    const fechaAperturaDOM = document.getElementById('fechaApertura');
    const totalGeneral = document.getElementById('totalGeneral');
    
    // Obtener los datos del DOM que ya están mostrados
    const saldoAcumuladoDiv = document.getElementById('saldo-acumulado');
    const saldoText = saldoAcumuladoDiv ? saldoAcumuladoDiv.textContent : '';
    
    // Parsear los saldos del texto "(EFECTIVO): Bs. 20.00"
    const saldos = {};
    let total = 0;
    
    // Usar regex para extraer forma de pago y monto
    const regex = /\(([^)]+)\):\s*Bs\.\s*([\d.,]+)/g;
    let match;
    
    while ((match = regex.exec(saldoText)) !== null) {
        const formaPago = match[1];
        const monto = parseFloat(match[2].replace(',', '.'));
        saldos[formaPago] = monto;
        total += monto;
    }
    
    // Usar la fecha real de apertura de la caja (la global viene de window.fechaApertura)
    if (window.fechaApertura && window.fechaApertura !== 'null' && window.fechaApertura !== '') {
        const fecha = new Date(window.fechaApertura);
        // Si no es válida o faltan datos
        if (isNaN(fecha.getTime())) {
            fechaAperturaDOM.textContent = 'Fecha Inválida (' + window.fechaApertura + ')';
        } else {
            fechaAperturaDOM.textContent = fecha.toLocaleString('es-ES');
        }
    } else {
        fechaAperturaDOM.textContent = 'N/A';
    }
    
    // Limpiar contenedor
    saldosContainer.innerHTML = '';
    
    // Crear tabla simple sin títulos
    let tablaHTML = `
        <table class="table">
            <tbody>
    `;
    
    // Iterar sobre los saldos encontrados
    for (const [formaPago, monto] of Object.entries(saldos)) {
        if (monto > 0) {
            tablaHTML += `
                <tr>
                    <td>${formaPago}</td>
                    <td style="text-align: right;">Bs. ${monto.toFixed(2)}</td>
                </tr>
            `;
        }
    }
    
    tablaHTML += `
            </tbody>
        </table>
    `;
    
    // Agregar la tabla
    saldosContainer.innerHTML = tablaHTML;
    totalGeneral.textContent = total.toFixed(2);
}
</script>
