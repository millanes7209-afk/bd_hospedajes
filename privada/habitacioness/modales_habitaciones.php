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
            <label for="cuentaID" class="form-label font-weight-bold">TIPO DE INGRESO (CUENTA):</label>
            <select class="form-control" id="cuentaID" name="cuentaID" required>
                <option value="">--SELECCIONE CUENTA--</option>
                <?php
                $extra_filter = "";
                if (isset($tieneModuloBanos) && $tieneModuloBanos) {
                    $extra_filter = " AND nombre NOT LIKE '%BAÑO%' AND nombre NOT LIKE '%BANO%' ";
                }
                $cuentas_ingreso = $db->obtenerTodo("SELECT cuentaID, nombre FROM cuentas WHERE tipo = 'INGRESO' AND empresaID = ? AND _estado <> 'X' AND codigo NOT IN ('401', '402') $extra_filter", [$empresaID]);
                foreach ($cuentas_ingreso as $c) {
                    $nombre_limpio = str_ireplace('INGRESO ', '', $c['nombre']);
                    echo "<option value='{$c['cuentaID']}'>" . mb_strtoupper($nombre_limpio) . "</option>";
                }
                ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="formaPagoID" class="form-label font-weight-bold">(*) Forma de Pago:</label>
            <select class="form-control" name="formaPagoID" id="formaPagoID" required>
                <option value="">-- SELECCIONE --</option>
                <?php
                $rs_fp2 = $db->obtenerTodo("SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A' AND empresaID = ?", [$empresaID]);
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
          <h5 class="modal-title" id="modal-egreso-label">Registrar Egreso</h5>
          <button type="button" class="close text-white" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label font-weight-bold">CATEGORÍA (CUENTA):</label>
            <select class="form-control" name="cuentaID" required>
                <option value="">--SELECCIONE CUENTA--</option>
                <?php
                $cuentas_egreso = $db->obtenerTodo("SELECT cuentaID, nombre FROM cuentas WHERE tipo = 'EGRESO' AND empresaID = ? AND _estado <> 'X'", [$empresaID]);
                foreach ($cuentas_egreso as $c) {
                    $nombre_limpio = str_ireplace(['EGRESO ', 'GASTO '], '', $c['nombre']);
                    echo "<option value='{$c['cuentaID']}'>" . mb_strtoupper($nombre_limpio) . "</option>";
                }
                ?>
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
            <label class="form-label font-weight-bold">Descripción / Detalle:</label>
            <input type="text" class="form-control" name="descripcion" placeholder="Ej: Pago de luz" oninput="this.value = this.value.toUpperCase()">
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

<!-- OTROS MODALES -->
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
          <input type="hidden" id="momentaneo-habitacionID" name="habitacionID">
          <input type="hidden" name="auth" value="habitaciones.php">

          <div class="mb-3">
            <label class="form-label fw-bold">Habitación</label>
            <input type="text" class="form-control bg-light" id="momentaneo-habitacion" name="descripcion" readonly>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">(*) Monto Total (Bs.)</label>
            <input type="number" class="form-control" name="monto_total" id="mom-monto_total"
                   step="0.5" min="1" required placeholder="0.00"
                   oninput="actualizarResumenMom()">
          </div>

          <!-- Sección de pagos mixtos -->
          <div class="card border-primary shadow-sm mb-3">
            <div class="card-header py-1 d-flex justify-content-between align-items-center">
              <small class="fw-bold">FORMA(S) DE PAGO</small>
              <button type="button" class="btn btn-xs btn-light py-0 px-1" onclick="agregarFilaPagoMom()" style="font-size: 0.65rem;">
                <i class="fas fa-plus"></i> AÑADIR
              </button>
            </div>
            <div class="card-body p-2">
              <div id="contenedorPagosMom"></div>
              <div class="border-top mt-2 pt-1">
                <div class="d-flex justify-content-between align-items-center small mb-0">
                  <span class="text-muted small">Pagado:</span>
                  <span class="fw-bold small">Bs <span id="momDisplayPagado">0.00</span></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small">SALDO:</span>
                  <span class="fw-bold small text-danger">Bs <span id="momDisplaySaldo">0.00</span></span>
                </div>
                <div id="momAlertaSaldo" class="alert alert-danger py-1 px-2 small mb-0 text-center fw-bold" style="display:none;"></div>
              </div>
            </div>
          </div>

          <!-- Template oculto de formas de pago para este modal -->
          <div id="templateFormaPagoMom" style="display:none;">
            <option value="">Seleccione Pago</option>
            <?php foreach ($rs_fp2 as $fp) echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>"; ?>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
        <button type="button" class="btn btn-info fw-bold" id="guardar-momentaneo">REGISTRAR</button>
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

<!-- Modal Pago de Deuda y Desocupar -->
<div class="modal fade" id="modal-pago-deuda" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="procesar_pago_deuda.php" method="post">
      <div class="modal-content shadow-sm">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">PAGAR DEUDA Y DESOCUPAR</h5>
          <button type="button" class="close" data-bs-dismiss="modal" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
          <div class="card border-0 bg-light mb-3">
            <div class="card-body py-2">
              <p class="mb-1 text-dark"><strong>Habitación:</strong> <span id="pago-deuda-habitacion"></span></p>
              <p class="mb-0 text-dark text-muted small">El monto es referencial. El recepcionista puede modificarlo según acuerdo con el cliente.</p>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">(*) Monto a Cobrar (Bs):</label>
            <input type="number" class="form-control" name="monto_total" id="pago-deuda-monto_total"
                   min="0" step="0.5" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">(*) Forma de Pago:</label>
            <select class="form-control" name="formaPagoID" required>
              <option value="">-- SELECCIONE --</option>
              <?php
              $rs_fp_deuda = $db->obtenerTodo("SELECT formaPagoID, tipo FROM formas_pago WHERE _estado = 'A' AND empresaID = ?", [$empresaID ?? 0]);
              foreach ($rs_fp_deuda as $fp) {
                  echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Campos ocultos -->
          <input type="hidden" name="habitacionID" id="pago-deuda-habitacionID">
          <input type="hidden" name="hospedajeID" id="pago-deuda-hospedajeID">
          <input type="hidden" name="habitacion_numero" id="pago-deuda-habitacion-numero">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CANCELAR</button>
          <button type="submit" class="btn btn-primary fw-bold">PAGAR Y DESOCUPAR</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para Baños -->
<div class="modal fade" id="modal-bano" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <form action="procesar_bano.php" method="post">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="bano-tipo-titulo">BAÑO</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="border:none; background:none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold">Monto (Bs.):</label>
            <input type="number" class="form-control form-control-lg text-center fw-bold" name="monto" value="1" step="0.5" required>
          </div>
          <!-- Descripción oculta forzada -->
          <input type="hidden" name="descripcion" value="SERVICIO DE BAÑOS">
          <input type="hidden" name="tipo" id="bano-tipo-input">
        </div>
        <div class="modal-footer">
          <button type="submit" id="btn-guardar-bano" class="btn btn-primary w-100">GUARDAR</button>
        </div>
      </div>
    </form>
  </div>
</div>
