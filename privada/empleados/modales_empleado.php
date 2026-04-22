<!-- modales_empleado.php -->

<!-- Modal de búsqueda por CI -->
<div class="modal fade" id="modalBusquedaCi" tabindex="-1" aria-labelledby="modalBusquedaCiLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBusquedaCiLabel">Buscar Empleado por C.I.</h5>
                <button type="button" class="btn-close" onclick="history.back()" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formBusquedaCi" method="GET">
                    <div class="mb-3">
                        <label for="ci_modal" class="form-label">Ingrese C.I. del Empleado:</label>
                        <input type="text" class="form-control" name="ci" id="ci_modal" 
                               placeholder="Ej: 12345678" 
                               onkeypress="if(event.keyCode==13){ this.form.submit(); }">
                    </div>
                    <div class="alert alert-info py-2">
                        <small><i class="fas fa-info-circle me-1"></i> Si la Empleado no está registrada, el sistema le permitirá crearla.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="realizarBusquedaAjax()">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Validación General -->
<div class="modal fade" id="modalValidacion" tabindex="-1" aria-labelledby="modalValidacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-warning">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="modalValidacionLabel"><i class="fas fa-exclamation-triangle me-2"></i> Aviso del Sistema</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p id="mensajeModal" class="lead"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
