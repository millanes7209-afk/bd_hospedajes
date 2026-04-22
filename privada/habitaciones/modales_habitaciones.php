<?php
/**
 * Modales para la gestión de habitaciones (Hospedaje, Permanencia, Pagos, etc.)
 */
?>

<!-- Modal de opciones -->
<div class="modal fade" id="menu-opciones" tabindex="-1" aria-labelledby="menu-opciones-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="menu-opciones-label">Seleccione una opción</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="modal-body-text">Por favor, elija una opción:</p>
      </div>
      <div class="modal-footer" id="modal-footer">
        <!-- Botones dinámicos -->
      </div>
    </div>
  </div>
</div>

<!-- Modal de Permanencia -->
<div class="modal fade" id="modal-permanencia" tabindex="-1" aria-labelledby="modal-permanencia-label" aria-hidden="true">
  <div class="modal-dialog">
    <!-- URL Actualizada al módulo de hospedajes -->
    <form action="../hospedajes/procesar_permanencia.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modal-permanencia-label">Extender Estadía (Permanencia)</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="permanencia-habitacion" class="form-label">Habitación</label>
            <input type="text" class="form-control" id="permanencia-habitacion" name="habitacion" readonly>
          </div>
          <div class="mb-3">
            <label for="permanencia-checkout" class="form-label">Fecha Salida</label>
            <input type="datetime-local" class="form-control" id="permanencia-checkout" name="checkout" required>
          </div>
          <div class="mb-3">
            <label for="permanencia-monto_pendiente" class="form-label">Monto</label>
            <input type="number" class="form-control" id="permanencia-monto_pendiente" name="monto_pendiente" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="permanencia-formaPagoID" class="form-label">Forma de Pago</label>
            <select class="form-control" id="permanencia-formaPagoID" name="formaPagoID" required>
              <option value="">Seleccione una forma de pago</option>
              <?php
              $sql_fp = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A'";
              $rs_fp = $db->obtenerTodo($sql_fp);
              foreach ($rs_fp as $fp) {
                  echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="descripcion" class="form-label"><b>(*)Descripción</b></label>
            <textarea class="form-control" name="descripcion" id="descripcion" rows="3" onkeyup="this.value=this.value.toUpperCase()"></textarea>
          </div>
          <div class="mb-3">
                <label class="form-label">Clientes</label>
                <ul id="clientes-lista" class="list-group"></ul>
            </div>
          <input type="hidden" id="permanencia-habitacionID" name="habitacionID">
          <input type="hidden" id="permanencia-precio" name="precio">
          <input type="hidden" id="permanencia-hospedajeID" name="hospedajeID">
          <input type="hidden" id="permanencia-clientesIDs" name="clientesIDs">
          <input type="hidden" id="permanencia-habitacion-numero" name="habitacion_numero">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Aceptar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para Registrar Otros Ingresos -->
<div class="modal fade" id="modal-ingreso" tabindex="-1" aria-labelledby="modal-ingreso-label" aria-hidden="true">
  <div class="modal-dialog">
    <!-- URL Actualizada al módulo de hospedajes (Lógica contable) -->
    <form action="../hospedajes/registrar_ingreso.php" method="post">
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
                if (!isset($rs_fp2)) {
                   $rs_fp2 = $db->obtenerTodo("SELECT formaPagoID,tipo FROM formas_pago WHERE _estado='A'");
                }
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
    <!-- URL Actualizada -->
    <form action="../hospedajes/procesar_pago_deuda.php" method="post">
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
    <!-- URL Actualizada -->
    <form action="../hospedajes/registrar_egreso.php" method="post">
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
        <!-- URL Actualizada -->
        <form id="form-momentaneo" action="../hospedajes/registrar_momentaneo.php" method="POST">
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
        <!-- Permanece en habitaciones ya que es un cambio de estado físico -->
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
