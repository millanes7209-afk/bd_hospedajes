<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

if (isset($_REQUEST['numero']) && isset($_REQUEST['tipo']) && isset($_REQUEST['precio'])) {
    $habitacion_numero = $_REQUEST['numero'];
    $tipo_habitacion = $_REQUEST['tipo'];
    $precio_habitacion = $_REQUEST['precio'];

    $empresaID = $_SESSION['empresaID'];
    $sql = "SELECT habitacionID FROM habitaciones WHERE numero = ? AND empresaID = ? AND _estado <> 'X'";
    $fila = $db->obtenerFila($sql, [$habitacion_numero, $empresaID]);

    if ($fila) {
        $habitacionID = $fila['habitacionID'];
    } else {
        echo "<div class='alert alert-danger'>Error: No se encontró la habitación número $habitacion_numero.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-warning'>No se han recibido los datos correctamente.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Hospedaje</title>

    <!-- Librerías -->
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src="../js/hospedaje_gestion.js"></script>
    <script src="../js/hospedaje_buscadores.js"></script>
    <script src="../js/hospedaje_pagos.js"></script>

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

        /* Garantizar alineación a la izquierda */
        .card-header h4 {
            text-align: left;
        }

        /* FORZAR NEGRO FUERTE EN SECCIÓN DE PAGOS */
        #contenedorPagos *,
        .card-body span,
        .card-body small {
            color: #000 !important;
            opacity: 1 !important;
        }
    </style>

    <script>
        // SEGURIDAD: Evitar que el 'Enter' registre formularios por accidente
        // NAVEGACIÓN CON ENTER (Enter como Tab)
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                var element = event.target;

                // Si es el buscador de CI, que siga funcionando el Enter para buscar
                if (element.id === 'ci') return;

                // Para cualquier otro input, select o textarea, saltar al siguiente
                if (['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)) {
                    event.preventDefault(); // Evitar envío del formulario

                    // Lista de elementos navegables (excluyendo el botón submit)
                    var form = element.form;
                    if (!form) return;

                    var elements = Array.from(form.elements).filter(el =>
                        !el.disabled &&
                        el.type !== 'hidden' &&
                        el.type !== 'submit' &&
                        el.tagName !== 'BUTTON'
                    );

                    var index = elements.indexOf(element);
                    if (index > -1 && index < elements.length - 1) {
                        elements[index + 1].focus();
                    }
                    return false;
                }
            }
        });

        // VALIDACIÓN MANDATARIA AL ENVIAR
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formHospedaje');
            if (form) {
                form.addEventListener('submit', function (event) {
                    const saldoText = document.getElementById('displaySaldoPendiente').innerText;
                    const saldo = parseFloat(saldoText) || 0;
                    const alertaSaldo = document.getElementById('alertaSaldo');

                    // Resetear la alerta en cada intento
                    if (alertaSaldo) {
                        alertaSaldo.style.display = 'none';
                        alertaSaldo.className = "alert alert-danger py-1 px-2 small mb-0 text-center fw-bold";
                    }

                    // 1. Validar que se haya seleccionado al menos un cliente
                    const clientes = document.querySelectorAll('#listaClientesSeleccionados input[name="clientesSeleccionados[]"]');
                    if (clientes.length === 0) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-user-times'></i> DEBE SELECCIONAR AL MENOS UN CLIENTE.";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }

                    // 2. Validar que existan filas de pago y que tengan forma de pago seleccionada
                    const filasPago = document.querySelectorAll('.fila-pago');
                    if (filasPago.length === 0) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-money-bill-wave'></i> DEBE AÑADIR AL MENOS UNA FORMA DE PAGO.";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }

                    let pagosIncompletos = false;
                    filasPago.forEach(fila => {
                        const fp = fila.querySelector('select').value;
                        const mm = fila.querySelector('input').value;
                        if (!fp || !mm || parseFloat(mm) <= 0) {
                            pagosIncompletos = true;
                        }
                    });

                    if (pagosIncompletos) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-exclamation-circle'></i> POR FAVOR, COMPLETE LOS DATOS DE PAGO (FORMA Y MONTO).";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }

                    // 3. Validar que el saldo sea 0
                    if (Math.abs(saldo) > 0.01) {
                        event.preventDefault();
                        if (alertaSaldo) {
                            alertaSaldo.innerHTML = "<i class='fas fa-balance-scale-right'></i> EL SALDO DEBE SER CERO. PAGUE EL MONTO TOTAL.";
                            alertaSaldo.style.display = 'block';
                        }
                        return false;
                    }
                });
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
                        <h4 class="mb-0">AGREGAR HOSPEDAJE</h4>
                    </div>
                    <div class="card-body">
                        <form id="formHospedaje" class="needs-validation" novalidate action="registrar_hospedaje.php"
                            method="post" name="formu">
                            <div class="row">
                                <!-- LADO IZQUIERDO: CLIENTES -->
                                <div class="col-md-5 border-end">
                                    <h5 class="border-bottom pb-2 mb-3">SELECCIONAR CLIENTE(S)</h5>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label for="paisID" class="form-label small fw-bold">País de Origen</label>
                                            <select class="form-control" name="paisID" id="paisID" autofocus>
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

                                    <!-- Registro de cliente -->
                                    <div id="seccionRegistro">
                                        <?php include("formulario_registro_cliente.php"); ?>
                                    </div>

                                    <!-- LISTA DE CLIENTES SELECCIONADOS (Unified Card) -->
                                    <div id="cardClientesSeleccionados" class="card mb-3" style="display: none;">
                                        <div class="card-header">
                                            <small class="">CLIENTES SELECCIONADOS</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaClientesSeleccionados"></div>
                                    </div>

                                    <div id="mensajeExito" class="alert alert-success mt-2 py-1 small"
                                        style="display:none;"></div>
                                    <div id="mensajeAlertaCliente" class="alert alert-danger mt-2 py-1 small"
                                        style="display: none;"></div>
                                </div>

                                <!-- LADO DERECHO: HOSPEDAJE -->
                                <div class="col-md-7 ps-md-4">
                                    <h5
                                        class="border-bottom pb-2 mb-3 d-flex justify-content-between align-items-center">
                                        <span>DATOS DEL HOSPEDAJE</span>
                                        <span class="text-dark">
                                            Habitación: <strong><?php echo $habitacion_numero; ?></strong> |
                                            Tipo: <strong><?php echo $tipo_habitacion; ?></strong>
                                        </span>
                                    </h5>

                                    <!-- FILA 1: TIPO Y DURACIÓN -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="tipo" class="form-label">(*) Tipo de Estadía</label>
                                            <select class="form-control" name="tipo" id="tipo"
                                                onchange="actualizarSalida()" required>
                                                <option value="HOSPEDAJE" selected>HOSPEDAJE</option>
                                                <option value="MOMENTANEO">MOMENTÁNEO</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6" id="contenedorDuracion" style="display: none;">
                                            <label for="duracion" class="form-label">(*) Duración</label>
                                            <select class="form-control" id="duracion" onchange="actualizarSalida()">
                                                <option value="1">1 hora</option>
                                                <option value="2">2 horas</option>
                                                <option value="3">3 horas</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- FILA 2: PRECIO Y FECHA SALIDA -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="monto_total" class="form-label"><b>(*) Precio (Bs)</b></label>
                                            <input type="number" class="form-control" name="monto_total"
                                                id="monto_total" value="<?php echo $precio_habitacion; ?>" min="1"
                                                step="0.5" oninput="actualizarResumenPagos()"
                                                data-original="<?php echo $precio_habitacion; ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="checkout" class="form-label"><b>(*) Fecha Salida</b></label>
                                            <input type="datetime-local" class="form-control" name="checkout"
                                                id="checkout" required>
                                        </div>
                                    </div>

                                    <!-- FILA 3: SECCIÓN DE PAGO -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card border-primary shadow-sm">
                                                <div
                                                    class="card-header py-1 d-flex justify-content-between align-items-center">
                                                    <small class="fw-bold small">PAGO</small>
                                                    <button type="button" class="btn btn-xs btn-light py-0 px-1"
                                                        onclick="agregarFilaPago()" style="font-size: 0.65rem;">
                                                        <i class="fas fa-plus"></i> AÑADIR
                                                    </button>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div id="contenedorPagos">
                                                        <!-- Las filas de pago se cargarán aquí por JS -->
                                                    </div>

                                                    <div class="border-top mt-2 pt-1">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center small mb-0">
                                                            <span class="text-muted small">Pagado:</span>
                                                            <span class="fw-bold small">Bs <span
                                                                    id="displayTotalPagado">0.00</span></span>
                                                        </div>
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-1">
                                                            <span class="fw-bold small">SALDO:</span>
                                                            <span class="fw-bold small text-danger">Bs <span
                                                                    id="displaySaldoPendiente">0.00</span></span>
                                                        </div>
                                                        <div id="alertaSaldo"
                                                            class="alert alert-danger py-1 px-2 small mb-0 text-center fw-bold"
                                                            style="display: none;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Template oculto para las formas de pago -->
                                    <div id="templateFormaPago" style="display: none;">
                                        <option value="">Seleccione Pago</option>
                                        <?php
                                        $empresa_actual = $_SESSION['empresaID'] ?? 0;
                                        $sql_fp = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado='A' AND empresaID = ?";
                                        $rs_fp = $db->obtenerTodo($sql_fp, [$empresa_actual]);
                                        foreach ($rs_fp as $fp) {
                                            echo "<option value='{$fp['formaPagoID']}'>{$fp['tipo']}</option>";
                                        }
                                        ?>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <label for="descripcion" class="form-label"><b>Descripción/Notas</b></label>
                                            <textarea class="form-control" name="descripcion" id="descripcion" rows="2"
                                                onkeyup="this.value=this.value.toUpperCase()"></textarea>
                                        </div>
                                    </div>
                                    <input type="hidden" name="habitacionID" value="<?php echo $habitacionID; ?>">
                                    <input type="hidden" name="tipohabitacionID"
                                        value="<?php echo $tipo_habitacion; ?>">
                                    <input type="hidden" name="habitacion_numero"
                                        value="<?php echo $habitacion_numero; ?>">

                                    <div class="d-flex justify-content-end gap-3 mt-4">
                                        <button class="btn btn-secondary px-4" type="button"
                                            onclick="window.history.back();">Atrás</button>
                                        <button class="btn btn-primary px-4 fw-bold" type="submit">Registrar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>