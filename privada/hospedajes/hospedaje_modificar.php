<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$hospedajeID = $_POST['hospedajeID'] ?? $_GET['hospedajeID'] ?? null;

if (!$hospedajeID) {
    echo "<div class='alert alert-danger'>Error: No se recibió el ID del hospedaje.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Hospedaje</title>

    <!-- Librerías -->
    <script type='text/javascript' src='../../ajax.js'></script>

    <style>
        /* TEXTO NEGRO FUERTE EN TODO EL FORMULARIO */
        body,
        label,
        input,
        select,
        textarea,
        .form-control,
        .form-select,
        h5,
        h4,
        h3,
        strong,
        p,
        span {
            color: #000 !important;
        }

        .card-header h4 {
            text-align: left;
        }

        /* Forzar visibilidad y contraste */
        .form-control {
            border: 1px solid #ced4da;
        }

        .saldo-indicador {
            font-size: 1.1rem;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        /* Botones deshabilitados */
        button:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            filter: grayscale(100%);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">MODIFICAR HOSPEDAJE</h4>
                        <span id="displayHabitacion" class="text-uppercase fw-bold text-muted small">Cargando
                            habitación...</span>
                    </div>
                    <div class="card-body">
                        <form id="formModificarHospedaje" action="hospedaje_modificar_procesar.php" method="post">
                            <input type="hidden" name="hospedajeID" value="<?php echo $hospedajeID; ?>">

                            <!-- FILA 1: ESTADO, CHECKOUT, MONTO -->
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label for="estado" class="form-label fw-bold">(*) Estado</label>
                                    <select class="form-control" name="estado" id="estado" required>
                                        <option value="ACTIVO">ACTIVO</option>
                                        <option value="INACTIVO">INACTIVO</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="checkout" class="form-label fw-bold">(*) Salida</label>
                                    <input type="datetime-local" class="form-control" name="checkout" id="checkout"
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label for="monto_total" class="form-label fw-bold">(*) Monto Pagado (Bs)</label>
                                    <input type="number" class="form-control" name="monto_total" id="monto_total"
                                        step="0.5" required oninput="recalcularTotal()">
                                </div>
                            </div>

                            <!-- FILA 2: HUESPEDES, PAGOS -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">HUESPEDES</h5>
                                    <div id="contenedorClientes" class="border p-3 rounded bg-light"
                                        style="min-height: 100px;">
                                        <p class="text-muted italic">Cargando huéspedes...</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2 mb-3">PAGOS / MOVIMIENTOS</h5>
                                    <div class="border rounded bg-light">
                                        <div id="contenedorPagos" class="p-3" style="min-height: 50px;">
                                            <p class="text-muted italic mb-0">Cargando pagos...</p>
                                        </div>
                                        <div id="contenedorSaldo" class="border-top p-2 text-center saldo-indicador bg-white">
                                            SALDO: <span id="saldoValor">0.00</span> Bs.
                                        </div>
                                        <div id="alertaSaldo" class="alert alert-danger py-1 px-2 small mb-0 text-center fw-bold"
                                            style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- FILA 3: OBSERVACIONES -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="descripcion" class="form-label fw-bold">Observaciones / Notas</label>
                                    <textarea class="form-control" name="descripcion" id="descripcion" rows="3"
                                        onkeyup="this.value=this.value.toUpperCase()"></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button class="btn btn-secondary px-4" style="margin-right: 15px;" type="button"
                                    onclick="window.history.back();">Atrás</button>
                                <button id="btnGuardar" class="btn btn-primary px-4" type="submit">Guardar
                                    Cambios</button>
                            </div>

                            <!-- Campo oculto para auditoría de precio -->
                            <input type="hidden" name="motivo_auditoria" id="motivo_auditoria"> 
                        </form>

                        <!-- MODAL PARA MOTIVO DE CAMBIO DE PRECIO -->
                        <div class="modal fade" id="modalMotivoPrecio" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog"> <!-- Movido arriba -->
                                <div class="modal-content">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title fw-bold"><i class="fas fa-shield-alt"></i> JUSTIFICACIÓN DE CAMBIO</h5>
                                    </div>
                                    <div class="modal-body text-black">
                                        <p>Se ha detectado una modificación financiera en este hospedaje.</p>
                                        <label class="form-label fw-bold small">Para continuar, ingrese el motivo del cambio:</label>
                                        <textarea class="form-control" id="txtMotivoPrecio" rows="3" placeholder="Ej: Error en registro inicial, descuento autorizado por gerencia..." onkeyup="this.value=this.value.toUpperCase()"></textarea>
                                        <div id="errorAudit" class="text-danger small fw-bold mt-2" style="display: none;">
                                            <i class="fas fa-exclamation-circle"></i> Por favor, ingrese un motivo válido (mínimo 5 caracteres).
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-primary" id="btnConfirmarAudit">Confirmar y Guardar</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Template oculto para las formas de pago -->
                        <div id="templateFormaPago" style="display: none;">
                            <option value="">Seleccione Pago</option>
                            <?php
                            $sql_fp = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A' AND empresaID = ?";
                            $rs_fp = $db->obtenerTodo($sql_fp, [$empresaID]);
                            foreach ($rs_fp as $fp) {
                                echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modales -->

<script>
    // DEPURADOR: MANDAR ID DE HOSPEDAJE DEL PHP AL JS CON RASTREO
    window.hospedajeID = <?php echo json_encode($hospedajeID); ?>;
    console.log("DEPURADOR [PHP a JS]: El ID del hospedaje a cargar es: " + window.hospedajeID);
</script>
<script src="js/hospedaje_modificar.js?v=<?php echo time(); ?>"></script>
</body>

</html>