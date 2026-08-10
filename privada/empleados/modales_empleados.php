<!-- MODAL BAJA LABORAL -->
<div class="modal" id="confirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>CONFIRMAR BAJA LABORAL</h5>
                <button id="closeModalBtn" class="btn-close btn-close-white"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div id="bajaStatus"></div>
                <div id="bajaBody">
                    <h4 class="text-danger mb-3" id="EmpleadoNombre"></h4>
                    <input type="hidden" id="EmpleadoID">
                    <div class="alert alert-warning small mb-0">
                        <i class="fas fa-info-circle me-1"></i> <b>Nota Importante:</b>
                        Esta acción marcará el contrato como <b>INACTIVO</b> en esta empresa y desactivará
                        automáticamente el acceso de su usuario al sistema.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button id="cancelModalBtn" class="btn btn-secondary fw-bold">CANCELAR</button>
                <button id="confirmDeleteBtn" class="btn btn-danger fw-bold">SÍ, DAR DE BAJA</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL RESET PASS -->
<div class="modal" id="modalResetPass">
    <div class="modal-dialog">
        <div class="modal-content border-success">
            <div class="modal-header bg-success text-white">
                <h5 class="mb-0 fw-bold"><i class="fas fa-key me-2"></i>RESETEAR CONTRASEÑA</h5>
                <button type="button" class="btn-close btn-close-white" onclick="hideResetModal()"></button>
            </div>
            <div class="modal-body py-4">
                <div id="resetStatus"></div>
                <div id="resetBody">
                    ¿Seguro que desea resetear la contraseña de <b id="resetUsuarioNombre"></b>?
                    <br><br>
                    La nueva clave será la general: <span class="text-success fw-bold">123456</span>
                </div>
            </div>
            <div class="modal-footer">
                <button id="cancelResetBtn" class="btn btn-secondary btn-sm" onclick="hideResetModal()">CANCELAR</button>
                <button id="confirmResetBtn" class="btn btn-success btn-sm fw-bold">RESETEAR AHORA</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL MODIFICAR CONTRATO -->
<div class="modal" id="modalContrato">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header">
                <h5 class="mb-0 fw-bold"><i class="fas fa-file-contract me-2"></i>AJUSTAR CONTRATO</h5>
                <button type="button" class="btn-close btn-close-white" onclick="hideContratoModal()"></button>
            </div>
            <div class="modal-body py-4">
                <div id="contratoStatus"></div>
                <div class="alert alert-danger py-2 mb-3 small">
                    <i class="fas fa-exclamation-circle me-1"></i> <b>AVISO DE SEGURIDAD:</b>
                    Use esta opción <u>ÚNICAMENTE</u> para corregir errores tipográficos.
                </div>
                <p class="mb-3">Empleado: <b id="contratoEmpleadoNombre"></b></p>
                <form id="formContrato">
                    <input type="hidden" name="empleadoID" id="contratoEmpleadoID">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cargo / Rol:</label>
                        <select name="rolID" id="contratoRolID" class="form-control border-dark">
                            <?php foreach ($roles_select as $r): ?>
                                <option value="<?php echo $r['rolID']; ?>"><?php echo htmlspecialchars($r['rol']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Sueldo Mensual (Bs.):</label>
                        <input type="number" name="sueldo" id="contratoSueldo" class="form-control border-dark" step="0.01" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="hideContratoModal()">CANCELAR</button>
                <button id="btnGuardarContrato" class="btn btn-primary btn-sm fw-bold">GUARDAR CAMBIOS</button>
            </div>
        </div>
    </div>
</div>
