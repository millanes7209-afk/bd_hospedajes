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
        <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="Close"></button>
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
    <form action="registrar_ingreso.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-ingreso-label">Registrar Ingreso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="tipo" class="form-label">TIPO</label>
            <select class="form-control" id="tipo" name="tipo" required>
                <option value="">--SELECCIONE--</option>
                <option value="BAÑO">BAÑO</option>
                <option value="ALQUILER">ALQUILER</option>
                <option value="DUCHA">DUCHA</option>
                <option value="VISITA">VISITA</option>
                <option value="MOMENTÁNEO">MOMENTÁNEO</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="formaPagoID" class="form-label">(*) Forma de Pago</label>
            <select class="form-control" name="formaPagoID" id="formaPagoID" required>
                <option value="">Seleccione Pago</option>
                <?php
                $rs_fp2 = $db->obtenerTodo("SELECT formaPagoID,tipo FROM formas_pago WHERE _estado='A'");
                foreach ($rs_fp2 as $fp) {
                    echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                }
                ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <input type="text" class="form-control" name="descripcion" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="mb-3">
            <label for="monto" class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" step="0.01" required>
          </div>
          <input type="hidden" name="tipo_movimiento" value="INGRESO">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Registrar Ingreso</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal de Pago de Deuda -->
<div class="modal fade" id="modal-pago-deuda" tabindex="-1" aria-labelledby="modal-pago-deuda-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="procesar_pago_deuda.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-pago-deuda-label">Pago de Deuda</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Habitación</label>
            <input type="text" class="form-control" id="pago-deuda-habitacion" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label">Monto a Pagar</label>
            <input type="number" class="form-control" id="pago-deuda-monto_total" name="monto_total" step="0.01" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Forma de Pago</label>
            <select class="form-control" id="pago-deuda-formaPagoID" name="formaPagoID" required>
              <option value="">Seleccione Pago</option>
              <?php
              $rs_fp3 = $db->obtenerTodo("SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A'");
              foreach ($rs_fp3 as $fp) {
                  echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
              }
              ?>
            </select>
          </div>
          <input type="hidden" id="pago-deuda-habitacionID" name="habitacionID">
          <input type="hidden" id="pago-deuda-hospedajeID" name="hospedajeID">
          <input type="hidden" id="pago-deuda-habitacion-numero" name="habitacion_numero">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Pagar y Desocupar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para Registrar Egreso -->
<div class="modal fade" id="modal-egreso" tabindex="-1" aria-labelledby="modal-egreso-label" aria-hidden="true">
  <div class="modal-dialog">
    <form action="registrar_egreso.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-egreso-label">Registrar Egreso</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">TIPO</label>
            <select class="form-control" name="tipo" required>
                <option value="">--SELECCIONE--</option>
                <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                <option value="LIMPIEZA">LIMPIEZA</option>
                <option value="ADELANTO">ADELANTO</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Forma de Pago</label>
            <select class="form-control" name="formaPagoID" required>
                <?php
                foreach ($rs_fp2 as $fp) {
                    echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                }
                ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" class="form-control" name="descripcion" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="mb-3">
            <label class="form-label">Monto</label>
            <input type="number" class="form-control" name="monto" step="0.01" required>
          </div>
          <input type="hidden" name="tipo_movimiento" value="EGRESO">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Registrar Egreso</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Registrar Momentáneo -->
<div class="modal fade" id="modal-momentaneo" tabindex="-1" aria-labelledby="modalmomentaneolabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Registrar Momentáneo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-momentaneo" action="registrar_momentaneo.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Habitación</label>
            <input type="text" class="form-control" id="momentaneo-habitacion" name="descripcion" readonly>
          </div>
          <input type="hidden" id="momentaneo-habitacionID" name="habitacionID">
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
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="form-mantenimiento" action="registrar_mantenimiento.php" method="POST">
          <div class="mb-3">
            <label class="form-label">Habitación</label>
            <input type="text" class="form-control" id="mantenimiento-habitacion" name="numero" readonly>
          </div>
          <input type="hidden" id="mantenimiento-habitacionID" name="habitacionID">
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
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="cambio-hospedajeID" name="hospedajeID" value="">
            <input type="hidden" id="cambio-habitacionID-actual" name="habitacionID_actual" value="">
            <input type="hidden" id="cambio-nueva-habitacion" name="nueva_habitacionID" value="">

            <p class="text-muted mb-2" style="font-size:0.85em;">Selecciona la habitación destino:</p>
            <!-- Grid de habitaciones disponibles (llenado por JS) -->
            <div id="grid-habitaciones-disponibles" class="d-flex flex-wrap gap-2">
                <!-- Botones generados por JS -->
            </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" id="btn-ejecutar-cambio" disabled><i class="fas fa-check"></i> Confirmar Cambio</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Notificaciones Notificación -->
<div id="miModal" class="modal">
    <div class="modal-contenido">
        <div class="modal-header">
            <span class="cerrar">&times;</span>
            <h2>EMITIR FACTURA!!!</h2>
        </div>
        <div class="modal-body">
            <p id="modalMensaje">...</p>
        </div>
        <div class="modal-footer">
            <button id="facturaEmitidaBtn">Factura Emitida</button>
            <button id="posponerBtn">Posponer</button>
        </div>
    </div>
</div>
