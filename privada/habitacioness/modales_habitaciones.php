<?php
/**
 * Modales para la gestión de habitaciones (Hospedaje, Permanencia, Pagos, etc.)
 */
?>

<!-- Modal de opciones -->
<div class="modal fade" id="menu-opciones" tabindex="-1" aria-labelledby="menu-opciones-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-light d-flex align-items-center">
        <h5 class="modal-title font-weight-bold" id="menu-opciones-label">
            <i class="fas fa-cogs text-secondary"></i> Opciones
        </h5>
        <h3 class="mb-0 ms-auto me-3 text-primary" id="modal-habitacion-badge"></h3>
        <button type="button" class="close" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
      </div>
      <div class="modal-footer" id="modal-footer" style="display: flex; flex-direction: column; align-items: stretch; gap: 8px;">
        <!-- Botones dinámicos -->
      </div>
    </div>
  </div>
</div>

<!-- Modal para Registrar Otros Ingresos -->
<div class="modal fade" id="modal-ingreso" tabindex="-1" aria-labelledby="modal-ingreso-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="procesar_movimiento.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-ingreso-label">Registrar Ingreso</h5>
          <button type="button" class="close" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="tipo" class="form-label font-weight-bold">TIPO DE INGRESO:</label>
            <select class="form-control" id="tipo" name="tipo" required>
                <option value="">--SELECCIONE--</option>
                <option value="BANO">BAÑO / DUCHA</option>
                <option value="VISITA">VISITA</option>
                <option value="OTRO">OTRO SERVICIO</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="formaPagoID" class="form-label font-weight-bold">(*) Forma de Pago:</label>
            <select class="form-control" name="formaPagoID" id="formaPagoID" required>
                <option value="">-- SELECCIONE --</option>
                <?php
                $rs_fp2 = $db->obtenerTodo("SELECT formaPagoID,tipo FROM formas_pago WHERE _estado='A'");
                foreach ($rs_fp2 as $fp) {
                    echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                }
                ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label font-weight-bold">Descripción / Detalle:</label>
            <input type="text" class="form-control" name="descripcion" placeholder="Ej: Baño simple" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="mb-3">
            <label for="monto" class="form-label font-weight-bold">Monto (Bs.):</label>
            <input type="number" class="form-control" name="monto" step="1" required placeholder="0.00">
          </div>
          <input type="hidden" name="tipo_movimiento" value="INGRESO">
          <input type="hidden" name="auth" value="habitaciones.php">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary font-weight-bold">REGISTRAR INGRESO</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para Registrar Egreso -->
<div class="modal fade" id="modal-egreso" tabindex="-1" aria-labelledby="modal-egreso-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="procesar_movimiento.php" method="post">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modal-egreso-label">Registrar Egreso / Gasto</h5>
          <button type="button" class="close text-white" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label font-weight-bold">CATEGORÍA DE GASTO:</label>
            <select class="form-control" name="tipo" required>
                <option value="">--SELECCIONE--</option>
                <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                <option value="INSUMOS">INSUMOS (LIMPIEZA, OTROS)</option>
                <option value="OTRO">OTRO GASTO</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label font-weight-bold">Efectivo / Tipo de Pago:</label>
            <select class="form-control" name="formaPagoID" required>
                <option value="">-- SELECCIONE --</option>
                <?php
                foreach ($rs_fp2 as $fp) {
                    echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                }
                ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label font-weight-bold">Descripción del Gasto:</label>
            <input type="text" class="form-control" name="descripcion" placeholder="Ej: Foco para pasillo" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="mb-3">
            <label class="form-label font-weight-bold">Monto a Retirar (Bs.):</label>
            <input type="number" class="form-control" name="monto" step="1" required placeholder="0.00">
          </div>
          <input type="hidden" name="tipo_movimiento" value="EGRESO">
          <input type="hidden" name="auth" value="habitaciones.php">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger font-weight-bold">REGISTRAR EGRESO</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- OTROS MODALES (Sin cambios en lógica pero con X arreglada) -->
<!-- Modal Registrar Momentáneo -->
<div class="modal fade" id="modal-momentaneo" tabindex="-1" aria-labelledby="modalmomentaneolabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Momentáneo</h5>
        <button type="button" class="close" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
      </div>
      <div class="modal-body">
        <form id="form-momentaneo" action="registrar_momentaneo.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Habitación</label>
            <input type="text" class="form-control" id="momentaneo-habitacion" name="descripcion" readonly>
          </div>
          <input type="hidden" id="momentaneo-habitacionID" name="habitacionID">
          <input type="hidden" name="auth" value="habitaciones.php">
          <div class="mb-3">
            <label class="form-label">Tipo</label>
            <input type="text" class="form-control" name="tipo" value="MOMENTANEO" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" class="form-control" name="monto" placeholder="Precio">
          </div>
          <div class="mb-3">
            <label class="form-label">Forma de Pago</label>
            <select class="form-control" name="formaPagoID" required>
                <option value="">-- SELECCIONE --</option>
                <?php foreach ($rs_fp2 as $fp) echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>"; ?>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" id="guardar-momentaneo">REGISTRAR</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Mantenimiento -->
<div class="modal fade" id="modal-mantenimiento" tabindex="-1" aria-labelledby="modalmantenimientolabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Mantenimiento</h5>
        <button type="button" class="close" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
      </div>
      <div class="modal-body">
        <form id="form-mantenimiento" action="registrar_mantenimiento.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Habitación</label>
            <input type="text" class="form-control" id="mantenimiento-habitacion" name="numero" readonly>
          </div>
          <input type="hidden" id="mantenimiento-habitacionID" name="habitacionID">
          <input type="hidden" name="auth" value="habitaciones.php">
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" class="form-control" id="mantenimiento-descripcion" name="descripcion" onkeyup="this.value=this.value.toUpperCase()">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" id="guardar-mantenimiento">REGISTRAR</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Cambio de Habitación -->
<div class="modal fade" id="modal-cambio-habitacion" tabindex="-1" aria-labelledby="modal-cambio-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="cambiar_habitacion.php" method="post">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="modal-cambio-label"><i class="fas fa-exchange-alt"></i> Cambiar Habitación &mdash; <span id="cambio-texto-actual"></span></h5>
          <button type="button" class="close text-white" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cambio-hospedajeID" name="hospedajeID" value="">
            <input type="hidden" id="cambio-habitacionID-actual" name="habitacionID_actual" value="">
            <input type="hidden" id="cambio-nueva-habitacion" name="nueva_habitacionID" value="">
            <input type="hidden" name="auth" value="habitaciones.php">

            <p class="text-muted mb-2" style="font-size:0.85em;">Selecciona la habitación destino:</p>
            <div id="grid-habitaciones-disponibles" class="d-flex flex-wrap gap-2"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="btn-ejecutar-cambio" disabled><i class="fas fa-check"></i> Confirmar Cambio</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Notificaciones -->
<div id="miModal" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <span class="cerrar" style="cursor:pointer; font-size: 2rem;">&times;</span>
            <h2>EMITIR FACTURA!!!</h2>
        </div>
        <div class="modal-body">
            <p id="modalMensaje">...</p>
        </div>
        <div class="modal-footer">
            <button id="facturaEmitidaBtn" class="btn btn-success">Factura Emitida</button>
            <button id="posponerBtn" class="btn btn-danger">Posponer</button>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Hospedaje -->
<div class="modal fade" id="modal-eliminar-hospedaje" tabindex="-1" aria-labelledby="modal-eliminar-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="eliminar_hospedaje.php" method="post">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="modal-eliminar-label"><i class="fas fa-trash-alt"></i> Eliminar Registro de Hospedaje</h5>
          <button type="button" class="close text-white" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body text-center">
            <input type="hidden" id="eliminar-hospedajeID" name="hospedajeID" value="">
            <input type="hidden" id="eliminar-habitacionID" name="habitacionID" value="">
            <input type="hidden" name="auth" value="habitaciones.php">
            
            <i class="fas fa-exclamation-triangle text-danger mb-3" style="font-size: 3rem;"></i>
            <h4 class="mb-3">¿Está seguro?</h4>
            <p class="lead">Se eliminará el hospedaje de la <strong>Habitación <span id="eliminar-numero-hab"></span></strong>.</p>
            <p class="text-muted small">Esta acción anulará el registro y pondrá la habitación en estado LIMPIEZA.</p>
            
            <div class="mb-3">
                <label class="form-label font-weight-bold">MOTIVO DE ELIMINACIÓN:</label>
                <textarea class="form-control" name="motivo" rows="2" placeholder="Explique brevemente por qué se elimina este hospedaje..." required oninput="this.value = this.value.toUpperCase()"></textarea>
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger font-weight-bold">ELIMINAR AHORA</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
        </div>
      </div>
    </form>
  </div>
</div>
