<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

/**
 * PANTALLA: AGREGAR HUÉSPED A ESTANCIA ACTIVA
 * Permite registrar nuevos acompañantes sin afectar cobros ni tiempos.
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
    <script type='text/javascript' src='../../ajax.js'></script>
    <script src="../js/hospedaje_buscadores.js"></script>
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
    </script>
    <style>
        body, label, input, select, textarea, .form-control, h5, strong { color: #000 !important; }
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
        .header-premium {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-lg border-0">
                    <div class="header-premium d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-white"><i class="fas fa-user-plus mr-2"></i> AÑADIR HUÉSPED / ACOMPAÑANTE</h4>
                        <span class="badge bg-light text-primary fs-6">Habitación <?php echo $hospedaje['numero']; ?></span>
                    </div>
                    <div class="card-body p-4">
                        <form action="procesar_agregar_huesped.php" method="post" id="formHospedaje">
                            <input type="hidden" name="hospedajeID" value="<?php echo $hospedajeID; ?>">
                            <input type="hidden" name="habitacion_numero" value="<?php echo $hospedaje['numero']; ?>">

                            <div class="row">
                                <!-- SECCIÓN IZQUIERDA: GESTIÓN DE PERSONAS -->
                                <div class="col-md-6 border-end">
                                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary">HUÉSPEDES EN LA HABITACIÓN</h5>
                                    
                                    <div id="listaClientesActuales" class="mb-4">
                                        <?php if(empty($clientes_actuales)): ?>
                                            <p class="text-muted italic">No hay huéspedes registrados.</p>
                                        <?php endif; ?>
                                        <?php foreach($clientes_actuales as $c): ?>
                                            <div class="cliente-badge">
                                                <span><i class="fas fa-check-circle text-success mr-2"></i> <strong><?php echo $c['ci']; ?></strong> - <?php echo $c['nombres']; ?> <?php echo $c['apellido1']; ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-success">BUSCAR / REGISTRAR NUEVO ACOMPAÑANTE</h5>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-5">
                                            <label class="form-label small fw-bold">País</label>
                                            <select class="form-control" name="paisID" id="paisID">
                                                <?php
                                                $sql_p = "SELECT paisID, nombre FROM paises WHERE _estado <> 'X' ORDER BY nombre ASC";
                                                $paises = $db->obtenerTodo($sql_p);
                                                foreach($paises as $p): ?>
                                                    <option value="<?php echo $p['paisID']; ?>" <?php echo ($p['nombre']=='BOLIVIA')?'selected':''; ?>><?php echo $p['nombre']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label small fw-bold">C.I. / Documento</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="ci" id="ci" placeholder="Número de CI..." onkeydown="if(event.key==='Enter'){event.preventDefault(); buscarCliente();}">
                                                <button type="button" class="btn btn-dark" onclick="buscarCliente()"><i class="fas fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contenedor para resultados de búsqueda -->
                                    <div id="resultadosBusqueda" class="mb-3"></div>

                                    <!-- Contenedor para alerta de errores en búsqueda -->
                                    <div id="mensajeAlertaCliente" class="alert alert-danger py-1 small" style="display:none;"></div>

                                    <!-- Lista de clientes a añadir (JS inyectará aquí) -->
                                    <div id="cardClientesSeleccionados" class="card bg-light mb-3" style="display:none;">
                                        <div class="card-header py-1 bg-success text-white small fw-bold">PERSONAS POR AÑADIR</div>
                                        <div class="list-group list-group-flush" id="listaClientesSeleccionados"></div>
                                    </div>

                                    <!-- Formulario oculto para registro rápido de cliente -->
                                    <div id="seccionRegistro" style="display:none;" class="bg-light p-3 border rounded">
                                        <?php include("formulario_registro_cliente.php"); ?>
                                    </div>
                                </div>

                                <!-- SECCIÓN DERECHA: RESUMEN Y CONFIRMACIÓN -->
                                <div class="col-md-6 ps-4">
                                    <div class="bg-light p-3 rounded mb-4">
                                        <h5 class="fw-bold text-dark"><i class="fas fa-info-circle mr-2"></i> RESUMEN ACTUAL</h5>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2"><strong>Tipo de Habitación:</strong> <?php echo $hospedaje['tipo_nombre']; ?></li>
                                            <li class="mb-2"><strong>Ingreso:</strong> <?php echo date('d/m/Y H:i', strtotime($hospedaje['checkin'])); ?></li>
                                            <li class="mb-2"><strong>Salida Pactada:</strong> <span class="text-danger fw-bold"><?php echo date('d/m/Y H:i', strtotime($hospedaje['checkout'])); ?></span></li>
                                            <li><strong>Monto Total Pactado:</strong> Bs. <?php echo number_format($hospedaje['monto'], 2); ?></li>
                                        </ul>
                                    </div>

                                    <div class="alert alert-warning border-warning shadow-sm">
                                        <h6 class="fw-bold"><i class="fas fa-exclamation-triangle"></i> IMPORTANTE</h6>
                                        <p class="mb-0 small">Este proceso solo registra la entrada de nuevos acompañantes. <strong>No genera cargos adicionales</strong> ni modifica la fecha de salida. Si desea cobrar extra o extender el tiempo, use el módulo de <strong>Permanencia</strong>.</p>
                                    </div>

                                    <div class="mt-5 d-flex justify-content-end gap-3">
                                        <a href="../habitacioness/habitaciones.php" class="btn btn-outline-secondary px-4 fw-bold">CANCELAR</a>
                                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">CONFIRMAR REGISTRO</button>
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
