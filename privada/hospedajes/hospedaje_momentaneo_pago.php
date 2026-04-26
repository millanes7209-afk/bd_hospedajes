<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Recibir datos básicos
$habitacionID = $_REQUEST['habitacionID'] ?? 0;
$modo = $_REQUEST['modo'] ?? 'deuda'; // 'deuda' o 'extension'

// Obtener datos del hospedaje activo (Momentáneo)
$sql = "SELECT h.*, hab.numero as habitacion_numero, thab.nombre as tipo_habitacion, thab.precio as precio_base
        FROM hospedajes h
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        JOIN tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        WHERE h.habitacionID = ? AND h.estado = 'ACTIVO' AND h._estado <> 'X'
        ORDER BY h.hospedajeID DESC LIMIT 1";
$hospedaje = $db->obtenerFila($sql, [$habitacionID]);

if (!$hospedaje) {
    echo "<div class='alert alert-danger'>Error: No se encontró un registro activo para esta habitación.</div>";
    exit;
}

// Calcular deuda si ha pasado el tiempo
$ahora = new DateTime();
$checkout_previsto = new DateTime($hospedaje['checkout']);
$es_deuda = ($ahora > $checkout_previsto);
$monto_deuda = 0;

if ($es_deuda) {
    // Lógica simple: Si se pasó, cobramos al menos una fracción o tarifa base
    // Aquí podrías personalizar según reglas del hotel. Por ahora, asumimos que debe pagar algo.
    $intervalo = $ahora->diff($checkout_previsto);
    $horas_atraso = $intervalo->h + ($intervalo->days * 24);
    if ($intervalo->i > 10) $horas_atraso++; // Tolerancia de 10 min
    
    // Tarifa de deuda (ejemplo: 20 Bs por hora de retraso, o simplemente el precio pactado)
    $monto_deuda = 30; // Monto base de multa/atraso
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pago/Extensión Momentáneo</title>
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src="../js/hospedaje_pagos.js"></script>
    <style>
        body, label, input, select, textarea, .form-control, h5, h4, h3, strong, p, span { color: #000 !important; }
        .card-header { background-color: #f8f9fa; border-bottom: 2px solid #dee2e6; }
        .info-box { background: #e9ecef; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .btn-tarifas .btn { min-height: 55px; font-weight: bold; }
        .btn-tarifas .btn small { display: block; font-weight: normal; font-size: 0.75rem; }
        .bg-deuda { background-color: #fff3cd; border: 1px solid #ffeeba; }
    </style>
</head>
<body onload="agregarFilaPago()">
    <div class="container mt-3 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary"><i class="fas fa-clock"></i> GESTIÓN DE MOMENTÁNEO</h4>
                        <span class="badge bg-dark fs-6">Habitación <?php echo $hospedaje['habitacion_numero']; ?></span>
                    </div>
                    <div class="card-body">
                        <form id="formMomentaneoPago" action="procesar_momentaneo_pago.php" method="post">
                            <input type="hidden" name="hospedajeID" value="<?php echo $hospedaje['hospedajeID']; ?>">
                            <input type="hidden" name="habitacionID" value="<?php echo $habitacionID; ?>">
                            <input type="hidden" name="modo_inicial" value="<?php echo $modo; ?>">

                            <div class="row">
                                <!-- RESUMEN ACTUAL -->
                                <div class="col-md-5">
                                    <h5 class="border-bottom pb-2 mb-3">ESTADO ACTUAL</h5>
                                    <div class="info-box <?php echo $es_deuda ? 'bg-deuda' : ''; ?>">
                                        <p class="mb-1"><strong>Ingreso:</strong> <?php echo date('H:i', strtotime($hospedaje['checkin'])); ?> (<?php echo date('d/m', strtotime($hospedaje['checkin'])); ?>)</p>
                                        <p class="mb-1"><strong>Salida Pactada:</strong> <?php echo date('H:i', strtotime($hospedaje['checkout'])); ?></p>
                                        <p class="mb-1"><strong>Monto Pagado:</strong> Bs. <?php echo number_format($hospedaje['monto'], 2); ?></p>
                                        
                                        <?php if ($es_deuda): ?>
                                            <hr>
                                            <div class="text-danger">
                                                <i class="fas fa-exclamation-circle"></i> <strong>TIEMPO AGOTADO</strong><br>
                                                Retraso: <?php echo $horas_atraso; ?> hora(s) aprox.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="border-bottom pb-2 mb-3">NUEVA ACCIÓN</h5>
                                    <div class="row g-2 btn-tarifas">
                                        <div class="col-12">
                                            <button type="button" class="btn btn-outline-danger w-100 mb-2" onclick="setAccion('SALIR', <?php echo $monto_deuda; ?>, 0)">
                                                SOLO PAGAR DEUDA Y SALIR
                                                <small>Monto: Bs. <?php echo $monto_deuda; ?></small>
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-primary w-100" onclick="setAccion('EXTENDER', <?php echo $monto_deuda + 30; ?>, 1)">
                                                + 1 HORA <small>Bs. <?php echo $monto_deuda + 30; ?></small>
                                            </button>
                                        </div>
                                        <div class="col-6">
                                            <button type="button" class="btn btn-outline-primary w-100" onclick="setAccion('EXTENDER', <?php echo $monto_deuda + 50; ?>, 2)">
                                                + 2 HORAS <small>Bs. <?php echo $monto_deuda + 50; ?></small>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <label class="form-label fw-bold">Observaciones:</label>
                                        <textarea name="nota" class="form-control" rows="2" placeholder="Ej: Pago de retraso..."></textarea>
                                    </div>
                                </div>

                                <!-- LIQUIDACIÓN Y PAGO -->
                                <div class="col-md-7 border-start ps-4">
                                    <h5 class="border-bottom pb-2 mb-3 text-success">LIQUIDACIÓN DE PAGO</h5>
                                    
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label class="fw-bold small">Total a Pagar (Bs):</label>
                                            <input type="number" id="monto_total" name="monto_total" class="form-control form-control-lg fw-bold text-primary" value="0" oninput="actualizarResumenPagos()">
                                            <small class="text-muted">Puedes editar el monto acordado.</small>
                                        </div>
                                        <div class="col-6">
                                            <label class="fw-bold small">Nueva Salida:</label>
                                            <input type="text" id="display_nueva_salida" class="form-control form-control-lg" value="-" readonly>
                                            <input type="hidden" id="nueva_salida" name="nueva_salida">
                                            <input type="hidden" id="tipo_accion" name="tipo_accion" value="">
                                        </div>
                                    </div>

                                    <!-- CAJA DE PAGOS -->
                                    <div class="card border-success shadow-sm">
                                        <div class="card-header py-2 d-flex justify-content-between align-items-center bg-success text-white">
                                            <span class="fw-bold small">FORMAS DE PAGO</span>
                                            <button type="button" class="btn btn-xs btn-light py-0 px-2" onclick="agregarFilaPago()">
                                                <i class="fas fa-plus"></i> Añadir
                                            </button>
                                        </div>
                                        <div class="card-body p-3">
                                            <div id="contenedorPagos"></div>
                                            
                                            <div class="mt-3 pt-2 border-top">
                                                <div class="d-flex justify-content-between fs-5">
                                                    <span>SALDO PENDIENTE:</span>
                                                    <span class="fw-bold text-danger">Bs. <span id="displaySaldoPendiente">0.00</span></span>
                                                </div>
                                                <div class="d-flex justify-content-between small text-muted">
                                                    <span>Total Ingresado:</span>
                                                    <span>Bs. <span id="displayTotalPagado">0.00</span></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4 d-flex gap-2">
                                        <button type="button" class="btn btn-secondary w-50" onclick="window.history.back()">CANCELAR</button>
                                        <button type="submit" id="btn-confirmar" class="btn btn-success w-50 fw-bold" disabled>CONFIRMAR PAGO</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Formas de Pago -->
    <div id="templateFormaPago" style="display: none;">
        <?php
        $rs_fp = $db->obtenerTodo("SELECT formapagoID, tipo FROM formas_pago WHERE _estado='A'");
        foreach ($rs_fp as $fp) echo "<option value='{$fp['formapagoID']}'>{$fp['tipo']}</option>";
        ?>
    </div>

    <script>
        function setAccion(tipo, monto, horasExt) {
            document.getElementById('tipo_accion').value = tipo;
            document.getElementById('monto_total').value = monto;
            
            let nuevaFecha = new Date();
            if (tipo === 'EXTENDER') {
                // Si extiende, sumamos las horas a la hora actual
                nuevaFecha.setHours(nuevaFecha.getHours() + horasExt);
                // Añadimos margen de cortesía (10 min por hora)
                nuevaFecha.setMinutes(nuevaFecha.getMinutes() + (horasExt * 10));
            } else {
                // Si solo paga deuda y sale, la salida es AHORA
                nuevaFecha = new Date();
            }

            // Formatear display
            let hh = String(nuevaFecha.getHours()).padStart(2, '0');
            let min = String(nuevaFecha.getMinutes()).padStart(2, '0');
            document.getElementById('display_nueva_salida').value = hh + ":" + min + (tipo === 'SALIR' ? ' (Check-out)' : ' (Extendida)');
            
            // Formatear para DB
            let yyyy = nuevaFecha.getFullYear();
            let mm = String(nuevaFecha.getMonth() + 1).padStart(2, '0');
            let dd = String(nuevaFecha.getDate()).padStart(2, '0');
            document.getElementById('nueva_salida').value = `${yyyy}-${mm}-${dd} ${hh}:${min}:00`;

            actualizarResumenPagos();
            
            // Resaltar botón seleccionado
            document.querySelectorAll('.btn-tarifas .btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
            document.querySelectorAll('.btn-tarifas .btn').forEach(b => b.classList.replace('btn-danger', 'btn-outline-danger'));
            
            const btnClick = event.currentTarget;
            if (tipo === 'SALIR') btnClick.classList.replace('btn-outline-danger', 'btn-danger');
            else btnClick.classList.replace('btn-outline-primary', 'btn-primary');
        }

        // Validación final
        document.getElementById('formMomentaneoPago').onsubmit = function(e) {
            const saldo = parseFloat(document.getElementById('displaySaldoPendiente').innerText) || 0;
            const tipo = document.getElementById('tipo_accion').value;

            if (!tipo) {
                alert("Por favor selecciona una acción (Pagar y salir o Extender).");
                e.preventDefault();
                return false;
            }

            if (Math.abs(saldo) > 0.01) {
                alert("El saldo debe estar en 0.00 para confirmar.");
                e.preventDefault();
                return false;
            }
        };

        // Escuchar cambios en saldos para habilitar botón
        const observer = new MutationObserver(function() {
            const saldo = parseFloat(document.getElementById('displaySaldoPendiente').innerText) || 0;
            const tipo = document.getElementById('tipo_accion').value;
            document.getElementById('btn-confirmar').disabled = (Math.abs(saldo) > 0.01 || !tipo);
        });
        observer.observe(document.getElementById('displaySaldoPendiente'), { childList: true });
    </script>
</body>
</html>
