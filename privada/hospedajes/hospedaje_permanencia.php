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

// 3. Lógica de Permanencia: Calcular nueva fecha (Checkout Anterior + 1 día)
$monto_deuda_transferida = $_POST['monto_deuda'] ?? 0;
$monto_defecto = ($monto_deuda_transferida > 0) ? $monto_deuda_transferida : $hospedaje['monto'];

$checkout_anterior = $hospedaje['checkout'];
$nueva_fecha_checkout = date('Y-m-d\TH:i', strtotime($checkout_anterior . ' +1 day'));
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
        body, label, input, select, textarea, .form-control, .form-select, h5, h4, h3, strong, p, span {
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        /* FORZAR NEGRO FUERTE EN SECCIÓN DE PAGOS */
        #contenedorPagos *, .card-body span, .card-body small {
            color: #000 !important;
            opacity: 1 !important;
        }
    </style>

    <script>
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
    </script>
</head>

<body onload="autocompletarCheckout()">
    <div class="container mt-2 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">REGISTRAR PERMANENCIA (EXTENSIÓN)</h4>
                    </div>
                    <div class="card-body">
                        <form action="procesar_permanencia.php" method="post" id="formHospedaje" class="needs-validation" novalidate>
                            <input type="hidden" name="hospedajeID_anterior" value="<?php echo $hospedajeID; ?>">
                            <input type="hidden" name="habitacionID" value="<?php echo $hospedaje['habitacionID']; ?>">
                            <input type="hidden" name="habitacion_numero" value="<?php echo $hospedaje['numero']; ?>">

                            <div class="row">
                                <!-- LADO IZQUIERDO: HUÉSPEDES -->
                                <div class="col-md-5 border-end">
                                    <h5 class="border-bottom pb-2 mb-3 text-primary">HUÉSPEDES EN LA HABITACIÓN</h5>
                                    
                                    <div id="listaClientesSeleccionados" class="mb-4">
                                        <?php if(empty($clientes_actuales)) echo "<p class='text-muted italic small'>No hay huéspedes registrados.</p>"; ?>
                                        <?php foreach($clientes_actuales as $c): ?>
                                            <div class="cliente-badge" id="itemCliente_<?php echo $c['clienteID']; ?>">
                                                <span><i class="fas fa-check-circle text-success mr-2"></i> <strong><?php echo $c['ci']; ?></strong> - <?php echo $c['nombres']; ?> <?php echo $c['apellido1']; ?></span>
                                                <input type="hidden" name="clientesSeleccionados[]" value="<?php echo $c['clienteID']; ?>">
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
                                                <input type="text" class="form-control" name="ci" id="ci" placeholder="CI..." 
                                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarCliente();}">
                                                <button type="button" class="btn btn-primary" onclick="buscarCliente()">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="resultadosBusqueda" class="mb-2"></div>
                                    <div id="mensajeAlertaCliente" class="alert alert-danger py-1 small" style="display:none;"></div>

                                    <!-- Registro de cliente -->
                                    <div id="seccionRegistro">
                                        <?php include("formulario_registro_cliente.php"); ?>
                                    </div>

                                    <div id="cardClientesSeleccionados" class="card mb-3 shadow-sm" style="display: none;">
                                        <div class="card-header py-1 bg-success text-white">
                                            <small class="fw-bold">NUEVOS ACOMPAÑANTES</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaNuevosAcompanantes"></div>
                                    </div>
                                </div>

                                <!-- LADO DERECHO: DATOS DE EXTENSIÓN -->
                                <div class="col-md-7 ps-md-4">
                                    <h5 class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                        <span>DETALLE DE PERMANENCIA</span>
                                        <span class="text-dark small">
                                            Habitación: <strong><?php echo $hospedaje['numero']; ?></strong> |
                                            Tipo: <strong><?php echo $hospedaje['tipo_nombre']; ?></strong>
                                        </span>
                                    </h5>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="checkout" class="form-label"><b>(*) SALIDA</b> <small class="text-muted ms-2">(Pagado hasta: <?php echo date('d/m/Y H:i', strtotime($checkout_anterior)); ?>)</small></label>
                                            <input type="datetime-local" class="form-control" name="checkout" id="checkout" 
                                                value="<?php echo $nueva_fecha_checkout; ?>" required onchange="actualizarResumenPagos()">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="monto_total" class="form-label"><b>(*) Monto Extensión (Bs)</b></label>
                                            <input type="number" class="form-control" name="monto_total" id="monto_total" 
                                                value="<?php echo $monto_defecto; ?>" min="0" step="0.5" 
                                                oninput="actualizarResumenPagos()"
                                                data-original="<?php echo $monto_defecto; ?>">
                                        </div>
                                    </div>

                                    <!-- SECCIÓN DE PAGO (Idéntico a nuevo) -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card border-primary shadow-sm">
                                                <div class="card-header py-1 d-flex justify-content-between align-items-center">
                                                    <small class="fw-bold small">PAGO DE LA EXTENSIÓN</small>
                                                    <button type="button" class="btn btn-xs btn-light py-0 px-1" onclick="agregarFilaPago()" style="font-size: 0.65rem;">
                                                        <i class="fas fa-plus"></i> AÑADIR
                                                    </button>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div id="contenedorPagos"></div>
                                                    <div class="border-top mt-2 pt-1 text-end">
                                                        <div class="small mb-1">
                                                            <span class="text-muted">Total Pago:</span> 
                                                            <span class="fw-bold">Bs <span id="displayTotalPagado">0.00</span></span>
                                                        </div>
                                                        <div class="fw-bold small">
                                                            <span>SALDO:</span>
                                                            <span class="text-danger">Bs <span id="displaySaldoPendiente">0.00</span></span>
                                                        </div>
                                                        <div id="alertaSaldo" class="alert alert-danger py-1 mt-1 small" style="display:none;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="descripcion" class="form-label"><b>Observaciones</b></label>
                                            <textarea class="form-control" name="descripcion" id="descripcion" rows="2" placeholder="Ej: Extensión de un día extra..." onkeyup="this.value=this.value.toUpperCase()"></textarea>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-3 mt-4">
                                        <button class="btn btn-secondary px-4 fw-bold" type="button" onclick="window.location.href='../habitacioness/habitaciones.php'">CANCELAR</button>
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
        $sql_fp = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A'";
        $rs_fp = $db->obtenerTodo($sql_fp);
        foreach ($rs_fp as $fp) echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
        ?>
    </div>

    <script>
        function autocompletarCheckout() {
            // En permanencia ya viene seteado por PHP (anterior + 1 día)
            // Solo disparamos el recálculo de saldo
            actualizarResumenPagos();
        }

        document.addEventListener('DOMContentLoaded', function () {
            autocompletarCheckout();

            const form = document.getElementById('formHospedaje');
            if (form) {
                form.addEventListener('submit', function (event) {
                    const saldoText = document.getElementById('displaySaldoPendiente').innerText;
                    const saldo = parseFloat(saldoText) || 0;
                    const alertaSaldo = document.getElementById('alertaSaldo');

                    if (alertaSaldo) alertaSaldo.style.display = 'none';

                    if (Math.abs(saldo) > 0.01) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-exclamation-triangle'></i> El saldo debe ser exactamente 0.00";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }
                });
            }

            // CORRECCIÓN PARA EL ERROR "NOT FOCUSABLE": Deshabilitar campos ocultos (Sin bucle infinito)
            const observer = new MutationObserver(function(mutations) {
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
</body>
</html>
