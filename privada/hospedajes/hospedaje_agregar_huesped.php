<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

/**
 * PANTALLA: AGREGAR HUÉSPED A ESTANCIA ACTIVA
 */

$hospedajeID = $_POST['hospedajeID'] ?? $_GET['hospedajeID'] ?? 0;

if (!$hospedajeID) {
    echo "<div class='alert alert-danger'>Error: No se recibió la referencia del hospedaje activo.</div>";
    exit;
}

// 1. Obtener datos del hospedaje y habitación
$sql = "SELECT h.*, hab.numero, thab.nombre as tipo_nombre
        FROM hospedajes h
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        JOIN tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        WHERE h.hospedajeID = ? AND h._estado <> 'X'";
$hospedaje = $db->obtenerFila($sql, [$hospedajeID]);

if (!$hospedaje) {
    echo "<div class='alert alert-danger'>Error: Hospedaje no encontrado en el sistema.</div>";
    exit;
}

// 2. Obtener clientes que ya están en la habitación
$sqlC = "SELECT c.* FROM hospedajes_clientes hc
         JOIN clientes c ON hc.clienteID = c.clienteID
         WHERE hc.hospedajeID = ? AND hc._estado <> 'X'";
$clientes_actuales = $db->obtenerTodo($sqlC, [$hospedajeID]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Huésped - Hab. <?php echo $hospedaje['numero']; ?></title>
    
    <!-- Librerías -->
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src="../js/hospedaje_buscadores.js"></script>
    <script src="../js/hospedaje_gestion.js"></script>

    <style>
        /* ESTÉTICA EXACTA A HOSPEDAJE_NUEVO.PHP */
        body, label, input, select, textarea, .form-control, h5, h4, h3, strong, p, span {
            color: #000 !important;
        }
        .card-header h4 { text-align: left; }
    </style>

    <script>
        // NAVEGACIÓN CON ENTER (Enter como Tab)
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
<body class="bg-light">
    <div class="container mt-2 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">AÑADIR HUÉSPED / ACOMPAÑANTE</h4>
                    </div>
                    <div class="card-body">
                        <form action="procesar_agregar_huesped.php" method="post" id="formHospedaje">
                            <input type="hidden" name="hospedajeID" value="<?php echo $hospedajeID; ?>">
                            <input type="hidden" name="habitacion_numero" value="<?php echo $hospedaje['numero']; ?>">

                            <div class="row">
                                <!-- LADO IZQUIERDO: CLIENTES (COPIA EXACTA DE HOSPEDAJE_NUEVO.PHP) -->
                                <div class="col-md-5 border-end">
                                    <h5 class="border-bottom pb-2 mb-3">SELECCIONAR CLIENTE(S)</h5>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label for="paisID" class="form-label small fw-bold">País de Origen</label>
                                            <select class="form-control" name="paisID" id="paisID" autofocus>
                                                <?php
                                                $sql_p = "SELECT paisID, nombre FROM paises WHERE _estado <> 'X' ORDER BY nombre ASC";
                                                $paises = $db->obtenerTodo($sql_p);
                                                foreach($paises as $p): ?>
                                                    <option value="<?php echo $p['paisID']; ?>" <?php echo ($p['nombre']=='BOLIVIA')?'selected':''; ?>><?php echo $p['nombre']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ci" class="form-label small fw-bold">C.I. / Documento</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="ci" id="ci" placeholder="CI..." onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarCliente();}">
                                                <button type="button" class="btn btn-primary" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
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
                                        <div class="card-header py-1">
                                            <small class="fw-bold">CLIENTES POR AÑADIR</small>
                                        </div>
                                        <div class="list-group list-group-flush" id="listaClientesSeleccionados"></div>
                                    </div>

                                    <div id="mensajeAlertaCliente" class="alert alert-danger mt-2 py-1 small" style="display: none;"></div>
                                </div>

                                <!-- LADO DERECHO: RESUMEN Y HUÉSPEDES ACTUALES -->
                                <div class="col-md-7 ps-md-4">
                                    <h5 class="border-bottom pb-2 mb-3">DATOS DE LA ESTANCIA - HAB. <?php echo $hospedaje['numero']; ?></h5>
                                    
                                    <div class="bg-light p-3 rounded mb-3 border">
                                        <h6 class="fw-bold text-muted mb-2">HUÉSPEDES ACTUALMENTE EN HABITACIÓN:</h6>
                                        <?php foreach($clientes_actuales as $c): ?>
                                            <div class="mb-1">
                                                <i class="fas fa-check-circle text-success me-2"></i> 
                                                <strong><?php echo $c['ci']; ?></strong> - <?php echo $c['nombres']; ?> <?php echo $c['apellido1']; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="alert alert-warning border-warning shadow-sm py-2">
                                        <h6 class="fw-bold mb-1"><i class="fas fa-exclamation-triangle"></i> IMPORTANTE:</h6>
                                        <p class="mb-0 small">Este proceso solo registra nuevos acompañantes. <b>No genera cargos adicionales</b> ni modifica la fecha de salida.</p>
                                    </div>

                                    <div class="mt-4 bg-white p-3 border rounded">
                                        <div class="row">
                                            <div class="col-6 mb-1 text-muted small">Ingreso:</div>
                                            <div class="col-6 mb-1 fw-bold small"><?php echo date('d/m/Y H:i', strtotime($hospedaje['checkin'])); ?></div>
                                            <div class="col-6 mb-1 text-muted small">Salida Pactada:</div>
                                            <div class="col-6 mb-1 fw-bold text-danger small"><?php echo date('d/m/Y H:i', strtotime($hospedaje['checkout'])); ?></div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-3 mt-5">
                                        <button class="btn btn-secondary px-4" type="button" onclick="window.location.href='../habitacioness/habitaciones.php'">CANCELAR</button>
                                        <button class="btn btn-primary px-5 fw-bold" type="submit">GUARDAR ACOMPAÑANTES</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const observer = new MutationObserver(function(mutations) {
                const frmReg = document.getElementById('formularioRegistro');
                if (frmReg) {
                    const isHidden = (window.getComputedStyle(frmReg).display === 'none');
                    frmReg.querySelectorAll('input, select, textarea').forEach(i => i.disabled = isHidden);
                }
            });
            const frmRegDiv = document.getElementById('formularioRegistro');
            if (frmRegDiv) {
                observer.observe(frmRegDiv, { attributes: true, attributeFilter: ['style'] });
                const isHidden = (window.getComputedStyle(frmRegDiv).display === 'none');
                frmRegDiv.querySelectorAll('input, select, textarea').forEach(i => i.disabled = isHidden);
            }
        });
    </script>
</body>
</html>
