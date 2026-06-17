<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Recibir ID del hospedaje actual para clonar datos
$hospedajeID = $_POST['hospedajeID'] ?? 0;

if (!$hospedajeID) {
    echo "<div class='alert alert-danger'>Error: No se recibió la referencia del hospedaje actual.</div>";
    exit;
}

// 1. Obtener datos actuales del hospedaje y habitación
$sql = "SELECT h.*, hab.numero, thab.nombre as tipo_nombre, thab.precio as precio_base
        FROM hospedajes h
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        JOIN tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        WHERE h.hospedajeID = ? AND h._estado <> 'X'";
$hospedaje = $db->obtenerFila($sql, [$hospedajeID]);

if (!$hospedaje) {
    echo "<div class='alert alert-danger'>Error: Hospedaje no encontrado.</div>";
    exit;
}

// 2. Obtener clientes actuales
$sqlC = "SELECT c.* FROM hospedajes_clientes hc
         JOIN clientes c ON hc.clienteID = c.clienteID
         WHERE hc.hospedajeID = ? AND hc._estado <> 'X'";
$clientes_actuales = $db->obtenerTodo($sqlC, [$hospedajeID]);

// 3. NUEVA LÓGICA: OBTENER O DEDUCIR EL PRECIO DIARIO PACTADO
$precio_diario_pactado = $hospedaje['precio_diario'];

// Si es NULL (registros antiguos), intentamos deducirlo para no dejar el campo en 0
if ($precio_diario_pactado === null || $precio_diario_pactado <= 0) {
    $fecha_in = new DateTime($hospedaje['checkin']);
    $fecha_out = new DateTime($hospedaje['checkout']);
    $intervalo = $fecha_in->diff($fecha_out);
    $dias_originales = $intervalo->days;
    // Si la estadía fue de menos de un día o tiene horas extra, contamos como 1 día mínimo para la deducción
    if ($intervalo->h >= 1 || $dias_originales == 0)
        $dias_originales++;

    $precio_diario_pactado = ($dias_originales > 0) ? ($hospedaje['monto'] / $dias_originales) : $hospedaje['precio_base'];
}

$checkout_anterior = $hospedaje['checkout'];
$nueva_fecha_checkout = date('Y-m-d\TH:i', strtotime($checkout_anterior . ' +1 day'));

// El monto inicial sugerido será: precio_pactado * días de extensión (por defecto 1 día)
$monto_defecto = $precio_diario_pactado;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Permanencia - Hab. <?php echo $hospedaje['numero']; ?></title>
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src="../js/hospedaje_gestion.js"></script>
    <script src="../js/hospedaje_buscadores.js"></script>
    <script src="../js/hospedaje_pagos.js"></script>
    <style>
        /* TEXTO NEGRO FUERTE EN TODO EL FORMULARIO (Igual que hospedaje_nuevo.php) */
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

        /*badges de clientes actuales*/
        .cliente-badge {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* FORZAR NEGRO FUERTE EN SECCIÓN DE PAGOS */
        #contenedorPagos *,
        .card-body span,
        .card-body small {
            color: #000 !important;
            opacity: 1 !important;
        }

        /* FORZAR VISIBILIDAD DE ERRORES (Rojo sobre el estilo negro) */
        .was-validated .form-control:invalid,
        .was-validated .form-select:invalid {
            border-color: #dc3545 !important;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(0.375em + 0.1875rem) center !important;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
        }

        .invalid-feedback {
            color: #dc3545 !important;
            font-weight: bold !important;
            display: none;
        }

        .was-validated :invalid~.invalid-feedback {
            display: block !important;
        }
    </style>

    <script>
        const PRECIO_DIARIO_PACTADO = <?php echo ($precio_diario_pactado > 0) ? $precio_diario_pactado : 0; ?>;
        const CHECKOUT_ANTERIOR = "<?php echo $checkout_anterior; ?>";

        function calcularMontoSugerido() {
            const inputPrecioDia = document.getElementById('precio_diario');
            const inputCheckout = document.getElementById('checkout');
            const inputMonto = document.getElementById('monto_total');

            if (!inputCheckout.value || !inputPrecioDia.value) return;

            const fechaAnterior = new Date(CHECKOUT_ANTERIOR);
            const fechaNueva = new Date(inputCheckout.value);
            const precioDia = parseFloat(inputPrecioDia.value) || 0;

            // Calcular diferencia en milisegundos
            const diff = fechaNueva - fechaAnterior;
            if (diff <= 0) {
                inputMonto.value = 0;
                actualizarResumenPagos();
                return;
            }

            // Lógica de cálculo de días: Bloques de 24h o fracción
            let dias = Math.ceil(diff / (1000 * 60 * 60 * 24));

            const nuevoMonto = dias * precioDia;
            inputMonto.value = nuevoMonto.toFixed(2);

            if (typeof actualizarResumenPagos === 'function') {
                actualizarResumenPagos();
            }
        }

        // SEGURIDAD: Evitar que el 'Enter' registre formularios por accidente (Igual que nuevo)

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                var element = event.target;
                if (element.id === 'ci') return;
                if (['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) {
                    event.preventDefault();
                    var form = element.form;
                    if (!form) return;
                    var elements = Array.from(form.elements).filter(el =>
                        !el.disabled && el.type !== 'hidden' && el.type !== 'submit' && el.tagName !== 'BUTTON'
                    );
                    var index = elements.indexOf(element);
                    if (index > -1 && index < elements.length - 1) {
                        elements[index + 1].focus();
                    }
                    return false;
                }
            }
        });

        /**
         * Permite quitar a un huésped de la lista de selección en permanencia
         */
        // Variable global para guardar temporalmente el ID del cliente a quitar
        let cID_a_quitar = null;

        function quitarAcompanante(clienteID) {
            const lista = document.getElementById('listaClientesSeleccionados');
            const items = lista.querySelectorAll('.cliente-badge');
            const alerta = document.getElementById('alertaMinimoCliente');

            // 1. Validar que no sea el último
            if (items.length <= 1) {
                if (alerta) {
                    alerta.style.display = 'block';
                    setTimeout(() => { alerta.style.display = 'none'; }, 3000);
                }
                return;
            }

            // 2. Abrir el Modal de Bootstrap en lugar de confirm()
            cID_a_quitar = clienteID;
            const myModal = new bootstrap.Modal(document.getElementById('modalConfirmarQuitar'));
            myModal.show();
        }

        function ejecutarRetiroHuesped() {
            if (cID_a_quitar) {
                const item = document.getElementById('itemCliente_' + cID_a_quitar);
                if (item) {
                    item.remove();
                }
                // Cerrar modal
                bootstrap.Modal.getInstance(document.getElementById('modalConfirmarQuitar')).hide();
                cID_a_quitar = null;
            }
        }
    </script>
</head>

<body>
    <div class="container mt-2 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">REGISTRAR PERMANENCIA (EXTENSIÓN)</h4>
                    </div>
                    <div class="card-body">
                        <form action="procesar_permanencia.php" method="post" id="formHospedaje"
                            class="needs-validation" novalidate>
                            <input type="hidden" name="hospedajeID_anterior" value="<?php echo $hospedajeID; ?>">
                            <input type="hidden" name="habitacionID" value="<?php echo $hospedaje['habitacionID']; ?>">
                            <input type="hidden" name="habitacion_numero" value="<?php echo $hospedaje['numero']; ?>">

                            <div class="row">
                                <!-- LADO IZQUIERDO: HUÉSPEDES -->
                                <div class="col-md-5 border-end">
                                    <h5 class="border-bottom pb-2 mb-3 text-primary">HUÉSPEDES EN LA HABITACIÓN</h5>

                                    <div id="listaClientesSeleccionados" class="mb-4">
                                        <div id="alertaMinimoCliente" class="alert alert-danger py-1 small mb-2"
                                            style="display:none;">
                                            <i class="fas fa-exclamation-triangle"></i> ¡ATENCIÓN! Debe quedar al menos
                                            un cliente en la habitación.
                                        </div>
                                        <?php if (empty($clientes_actuales))
                                            echo "<p class='text-muted italic small'>No hay huéspedes registrados.</p>"; ?>
                                        <?php foreach ($clientes_actuales as $c): ?>
                                            <div class="cliente-badge" id="itemCliente_<?php echo $c['clienteID']; ?>">
                                                <span><i class="fas fa-check-circle text-success mr-2"></i>
                                                    <strong><?php echo $c['ci']; ?></strong> - <?php echo $c['nombres']; ?>
                                                    <?php echo $c['apellido1']; ?></span>
                                                <!-- Botón para quitar acompañante -->
                                                <button type="button" class="btn btn-xs btn-outline-danger border-0 py-0"
                                                    onclick="quitarAcompanante(<?php echo $c['clienteID']; ?>)"
                                                    title="Quitar de la estadía">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                                <input type="hidden" name="clientesSeleccionados[]"
                                                    value="<?php echo $c['clienteID']; ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <h5 class="border-bottom pb-1 mb-3 small fw-bold">AÑADIR NUEVO ACOMPAÑANTE</h5>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label for="paisID" class="form-label small fw-bold">País de Origen</label>
                                            <select class="form-control" name="paisID" id="paisID">
                                                <?php
                                                $sql_paises = "SELECT paisID, nombre FROM paises WHERE _estado <> 'X' ORDER BY nombre ASC";
                                                $rs_paises = $db->ejecutar($sql_paises);
                                                while ($fila_p = $rs_paises->fetch()): ?>
                                                    <option value="<?php echo $fila_p['paisID']; ?>" <?php echo ($fila_p['nombre'] == 'BOLIVIA') ? 'selected' : ''; ?>>
                                                        <?php echo $fila_p['nombre']; ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ci" class="form-label small fw-bold">C.I. / Documento</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="ci" id="ci"
                                                    placeholder="CI..."
                                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarCliente();}">
                                                <button type="button" class="btn btn-primary" onclick="buscarCliente()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="resultadosBusqueda" class="mb-2"></div>
                                    <div id="mensajeAlertaCliente" class="alert alert-danger py-1 small"
                                        style="display:none;"></div>

                                    <!-- Registro de cliente -->
                                    <div id="seccionRegistro">
                                        <?php include("formulario_registro_cliente.php"); ?>
                                    </div>

                                    <div id="cardClientesSeleccionados" class="card mb-3 shadow-sm"
                                        style="display: none;">
                                        <div class="card-header py-1 bg-success text-white">
                                            <small class="fw-bold">NUEVOS ACOMPAÑANTES</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaNuevosAcompanantes"></div>
                                    </div>
                                </div>

                                <!-- LADO DERECHO: DATOS DE EXTENSIÓN -->
                                <div class="col-md-7 ps-md-4">
                                    <h5
                                        class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                        <span>DETALLE DE PERMANENCIA</span>
                                        <span class="text-dark small">
                                            Habitación: <strong><?php echo $hospedaje['numero']; ?></strong> |
                                            Tipo: <strong><?php echo $hospedaje['tipo_nombre']; ?></strong>
                                        </span>
                                    </h5>

                                    <div class="row mb-3 align-items-end">
                                        <div class="col-md-4">
                                            <label for="precio_diario" class="form-label fw-bold">Precio por Día (Bs)</label>
                                            <input type="number" class="form-control" name="precio_diario"
                                                id="precio_diario" value="<?php echo $precio_diario_pactado; ?>"
                                                step="any" min="0" required oninput="calcularMontoSugerido()">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="checkout" class="form-label fw-bold">Nueva Salida
                                                <small class="text-muted fw-normal ms-1">(Anterior:
                                                    <?php echo date('d/m/Y H:i', strtotime($checkout_anterior)); ?>)</small>
                                            </label>
                                            <input type="datetime-local" class="form-control" name="checkout"
                                                id="checkout" value="<?php echo $nueva_fecha_checkout; ?>" required
                                                onchange="calcularMontoSugerido()">
                                            <div class="invalid-feedback">Defina la nueva fecha de salida.</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="monto_total" class="form-label fw-bold">(*) Monto a Cobrar
                                                (Bs)</label>
                                            <input type="number" class="form-control" name="monto_total"
                                                id="monto_total" value="<?php echo $monto_defecto; ?>" min="0"
                                                step="any" required oninput="actualizarResumenPagos()">
                                            <div class="invalid-feedback">Ingrese el monto del cobro.</div>
                                        </div>
                                    </div>

                                    <!-- SECCIÓN DE PAGO (Idéntico a nuevo) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card border-primary shadow-sm">
                                                <div
                                                    class="card-header py-1 d-flex justify-content-between align-items-center">
                                                    <small class="fw-bold small">PAGO DE LA EXTENSIÓN</small>
                                                    <button type="button" class="btn btn-xs btn-light py-0 px-1"
                                                        onclick="agregarFilaPago()" style="font-size: 0.65rem;">
                                                        <i class="fas fa-plus"></i> AÑADIR
                                                    </button>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div id="contenedorPagos"></div>
                                                    <div class="border-top mt-2 pt-1 text-end">
                                                        <div class="small mb-1">
                                                            <span class="text-muted">Total Pago:</span>
                                                            <span class="fw-bold">Bs <span
                                                                    id="displayTotalPagado">0.00</span></span>
                                                        </div>
                                                        <div class="fw-bold small">
                                                            <span>SALDO:</span>
                                                            <span class="text-danger">Bs <span
                                                                    id="displaySaldoPendiente">0.00</span></span>
                                                        </div>
                                                        <div id="alertaSaldo" class="alert alert-danger py-1 mt-1 small"
                                                            style="display:none;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-light border-0 shadow-sm mb-3">
                                                <div class="card-body p-3">
                                                    <h6 class="fw-bold mb-2 text-secondary"><i
                                                            class="fas fa-info-circle"></i> REFERENCIA DE COBRO</h6>
                                                    <div
                                                        class="d-flex justify-content-between small text-dark border-bottom pb-1 mb-1">
                                                        <span>Precio pactado anterior:</span>
                                                        <span class="fw-bold">Bs.
                                                            <?= number_format($precio_diario_pactado, 2) ?> / día</span>
                                                    </div>
                                                    <div
                                                        class="d-flex justify-content-between small text-dark border-bottom pb-1 mb-1">
                                                        <span>Vencimiento original:</span>
                                                        <span
                                                            class="fw-bold text-danger"><?= date('d/m/Y H:i', strtotime($checkout_anterior)) ?></span>
                                                    </div>
                                                    <?php
                                                    $hoy_obj = new DateTime();
                                                    $checkout_ant_obj = new DateTime($checkout_anterior);
                                                    $dif_total = $checkout_ant_obj->diff($hoy_obj);
                                                    $dias_totales_deuda = $dif_total->days;
                                                    if ($dif_total->invert == 0 && ($dif_total->h > 0 || $dif_total->i > 0))
                                                        $dias_totales_deuda++;
                                                    $monto_total_deuda = $dias_totales_deuda * $precio_diario_pactado;
                                                    ?>
                                                    <div class="d-flex justify-content-between small text-dark">
                                                        <span>Deuda total hasta hoy (<?= $dias_totales_deuda ?>
                                                            días):</span>
                                                        <span class="fw-bold text-danger">Bs.
                                                            <?= number_format($monto_total_deuda, 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-3 mt-4">
                                        <button class="btn btn-secondary px-4 fw-bold" type="button"
                                            onclick="window.location.href='../habitacioness/habitaciones.php'">CANCELAR</button>
                                        <button class="btn btn-success px-4 fw-bold" type="submit">REGISTRAR</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template para formas de pago -->
    <div id="templateFormaPago" style="display: none;">
        <option value="">Seleccione Pago</option>
        <?php
        $empresa_actual = $_SESSION['empresaID'] ?? 0;
        $sql_fp = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A' AND empresaID = ?";
        $rs_fp = $db->obtenerTodo($sql_fp, [$empresa_actual]);
        foreach ($rs_fp as $fp)
            echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
        ?>
    </div>

    <script>
        function autocompletarCheckout() {
            // En permanencia ya viene seteado por PHP (anterior + 1 día)
            // Solo disparamos el recálculo de saldo
            agregarFilaPago(); // Asegurar que exista la primera fila de pagos
            actualizarResumenPagos();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Recalcular saldo inicial
            autocompletarCheckout();

            // Salto de TAB desde Monto Total
            const montoInput = document.getElementById('monto_total');
            if (montoInput) {
                montoInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Tab' && !e.shiftKey) {
                        e.preventDefault();
                        const primerPago = document.querySelector('#contenedorPagos select');
                        if (primerPago) {
                            primerPago.focus();
                        } else {
                            const btnAdd = document.querySelector('button[onclick="agregarFilaPago()"]');
                            if (btnAdd) btnAdd.focus();
                        }
                    }
                });
            }

            const form = document.getElementById('formHospedaje');
            if (form) {
                form.addEventListener('submit', function (event) {
                    const alertaSaldo = document.getElementById('alertaSaldo');
                    if (alertaSaldo) alertaSaldo.style.display = 'none';

                    // 1. VALIDACIÓN NATIVA (CAMPOS VACÍOS O INVÁLIDOS)
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        form.classList.add('was-validated');
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-exclamation-circle'></i> <b>¡ATENCIÓN!</b> Falta completar campos obligatorios.";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }

                    // 2. VALIDACIÓN DE SALDO CERO
                    const saldo = parseFloat(document.getElementById('displaySaldoPendiente').innerText) || 0;
                    if (Math.abs(saldo) > 0.01) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-exclamation-triangle'></i> <b>¡ERROR!</b> El saldo debe ser 0.00";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }
                    form.classList.add('was-validated');
                });
            }

            // CORRECCIÓN PARA EL ERROR "NOT FOCUSABLE": Deshabilitar campos ocultos (Sin bucle infinito)
            const observer = new MutationObserver(function (mutations) {
                const frmReg = document.getElementById('formularioRegistro');
                if (frmReg) {
                    const isHidden = (window.getComputedStyle(frmReg).display === 'none');
                    const inputs = frmReg.querySelectorAll('input, select, textarea');
                    inputs.forEach(input => {
                        if (input.disabled !== isHidden) {
                            input.disabled = isHidden;
                        }
                    });
                }
            });
            const config = { attributes: true, attributeFilter: ['style'] }; // Solo observar cambios de estilo
            const frmRegDiv = document.getElementById('formularioRegistro');
            if (frmRegDiv) {
                observer.observe(frmRegDiv, config);
                // Ejecutar una vez al inicio para sincronizar
                const isHidden = (window.getComputedStyle(frmRegDiv).display === 'none');
                frmRegDiv.querySelectorAll('input, select, textarea').forEach(i => i.disabled = isHidden);
            }
        });
    </script>
    <!-- MODAL BOOTSTRAP PARA CONFIRMACIÓN (DISEÑO MINIMALISTA) -->
    <div class="modal fade" id="modalConfirmarQuitar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-4">
                    <div class="mb-3 text-danger">
                        <i class="fas fa-user-minus fa-3x"></i>
                    </div>
                    <h5 class="modal-title mb-2 fw-bold text-dark">¿Retirar Huésped?</h5>
                    <p class="small text-muted mb-4">El cliente ya no formará parte de la extensión de estadía.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-danger fw-bold" onclick="ejecutarRetiroHuesped()">SÍ,
                            RETIRAR</button>
                        <button type="button" class="btn btn-light text-dark fw-bold"
                            data-bs-dismiss="modal">CANCELAR</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>