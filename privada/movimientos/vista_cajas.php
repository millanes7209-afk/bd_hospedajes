<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
require_once("../hospedajes/utils/hospedajes_utilidades.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['sesion_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener roles y parámetros de filtro
$rol_usuario = $_SESSION['sesion_rol'] ?? '';
$empresaID_filtro = $_SESSION['empresaID'];
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$usuarioID_filtro = $_GET['usuarioID'] ?? '';

// Obtener usuarioID actual
$usuarioID_actual = $_SESSION['sesion_id_usuario'] ?? $_SESSION['usuarioID'] ?? 0;

// Obtener usuarios para filtro si tiene privilegios
$usuarios_mov = [];
    // Obtener usuarios solo de la empresa actual
    $sql_usuarios = "SELECT DISTINCT u.usuarioID, u.usuario 
                     FROM usuarios u
                     INNER JOIN empleado_empresas ee ON u.empleadoID = ee.empleadoID
                     WHERE u._estado <> 'X' AND ee.empresaID = ? AND ee._estado <> 'X'
                     ORDER BY u.usuario";
    $usuarios_mov = $db->obtenerTodo($sql_usuarios, [$empresaID_filtro]);

// Obtener formas de pago disponibles
$sql_formas_pago = "SELECT fp.formapagoID, fp.tipo 
                   FROM formas_pago fp 
                   WHERE fp.empresaID = ? AND fp._estado <> 'X'
                   ORDER BY fp.tipo";
$formas_pago = $db->obtenerTodo($sql_formas_pago, [$empresaID_filtro]);

// Generar vista semanal basada en movimientos
$vista_semanal = [];
$fechas_rango = [];

// Array para sumatorias finales (footer)
$suma_footer_formas = [];
$suma_footer_total_general = 0;
foreach ($formas_pago as $fp) {
    $suma_footer_formas[$fp['tipo']] = 0;
}

// Generar rango de fechas
$fecha_actual = new DateTime($fecha_inicio);
$fecha_fin_obj = new DateTime($fecha_fin);
while ($fecha_actual <= $fecha_fin_obj) {
    $fecha_str = $fecha_actual->format('Y-m-d');
    $fechas_rango[] = $fecha_str;
    $vista_semanal[$fecha_str] = [
        'fecha' => $fecha_str,
        'movimientos' => []
    ];
    $fecha_actual->add(new DateInterval('P1D'));
}

// Para cada fecha, obtener movimientos
foreach ($fechas_rango as $fecha) {
    // FILTRO ESTRICTO: Cajas con ingresos/egresos de habitaciones O de baños pendientes de entrega
    $where_entrega = " AND (EXISTS (SELECT 1 FROM ingresos i WHERE i.cajaID = c.cajaID AND i.entregado = 0 AND i._estado <> 'X') 
                         OR EXISTS (SELECT 1 FROM egresos e WHERE e.cajaID = c.cajaID AND e.entregado = 0 AND e._estado <> 'X')
                         OR EXISTS (SELECT 1 FROM banos b WHERE b.cajaID = c.cajaID AND b.entregado = 0)) ";

    // Armar consulta según el rol (Filtrar por el RESPONSABLE DEL CIERRE)
    if ($rol_usuario === 'RECEPCIONISTA') {
        $where_user = "AND cc._usuario = ?";
        $params_mov = [$fecha, $empresaID_filtro, $usuarioID_actual];
    } else {
        if (!empty($usuarioID_filtro)) {
            $where_user = "AND cc._usuario = ?";
            $params_mov = [$fecha, $empresaID_filtro, $usuarioID_filtro];
        } else {
            $where_user = "";
            $params_mov = [$fecha, $empresaID_filtro];
        }
    }

    $sql_movimientos_dia = "SELECT 
                              cc.monto,
                              'INGRESO' as mov_tipo, 
                              u.usuario as nombre_usuario,
                              fp.tipo as forma_pago,
                              cc.cajaID,
                              c.fecha_apertura
                            FROM cierre_cajas cc
                            INNER JOIN cajas c ON cc.cajaID = c.cajaID
                            INNER JOIN usuarios u ON c.usuarioID = u.usuarioID
                            INNER JOIN formas_pago fp ON cc.formapagoID = fp.formapagoID
                            WHERE DATE(c.fecha_cierre) = ? 
                              AND c.empresaID = ? 
                              AND c.estado = 'CERRADA'
                              AND c._estado <> 'X'
                              AND cc._estado <> 'X'
                              $where_user
                              $where_entrega
                            ORDER BY c.fecha_cierre DESC";

    $movimientos_dia = $db->obtenerTodo($sql_movimientos_dia, $params_mov);

    // Agrupar movimientos por usuario y caja
    $agrupados = [];
    foreach ($movimientos_dia as $mov) {
        $clave = $mov['cajaID'];

        if (!isset($agrupados[$clave])) {
            $agrupados[$clave] = [
                'usuario' => $mov['nombre_usuario'],
                'fecha_apertura' => $mov['fecha_apertura'],
                'saldos' => [],
                'movimientos_count' => 0,
                'cajaID' => $mov['cajaID']
            ];
        }

        // Acumular saldos por forma de pago
        $forma_pago = $mov['forma_pago'];
        if (!isset($agrupados[$clave]['saldos'][$forma_pago])) {
            $agrupados[$clave]['saldos'][$forma_pago] = 0;
        }

        // Sumar o restar según tipo de movimiento
        if ($mov['mov_tipo'] == 'INGRESO') {
            $agrupados[$clave]['saldos'][$forma_pago] += $mov['monto'];
        } else {
            $agrupados[$clave]['saldos'][$forma_pago] -= $mov['monto'];
        }

        $agrupados[$clave]['movimientos_count']++;
    }

    $agrupados_final = array_values($agrupados);

    // NUEVO: Obtener saldo de BAÑOS por cada cajaID encontrada
    foreach ($agrupados_final as &$caja_data) {
        $cid = $caja_data['cajaID'];
        $sql_b = "SELECT SUM(CASE WHEN tipo = 'INGRESO' THEN monto ELSE 0 END) - 
                         SUM(CASE WHEN tipo = 'EGRESO' THEN monto ELSE 0 END) as saldo
                  FROM banos WHERE cajaID = ? AND entregado = 0";
        $rb = $db->obtenerFila($sql_b, [$cid]);
        $caja_data['saldo_bano'] = (float)($rb['saldo'] ?? 0);
    }

    $vista_semanal[$fecha]['movimientos'] = $agrupados_final;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<style>
    thead {
        color: black !important;
        background: #b5b5b5 !important;
    }

    .card {
        margin: 20px;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
        border: 0 !important;
    }

    .tabla-turno th, .tabla-turno td { vertical-align: middle !important; }



    #recaudacion-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #212529;
        color: white;
        padding: 15px;
        display: none;
        z-index: 1000;
        box-shadow: 0 -5px 15px rgba(0, 0, 0, 0.3);
    }

    @media print {
        /* Ocultar TODO por defecto */
        body * { visibility: hidden; }
        
        /* Mostrar solo el contenedor del reporte y sus hijos */
        #area-impresion, #area-impresion * { visibility: visible; }
        
        /* Posicionar el área de impresión al inicio de la página */
        #area-impresion {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
        }

        /* Ajustes de estilo para la tabla */
        .table { font-size: 11px; width: 100% !important; border-collapse: collapse !important; }
        .table th, .table td { border: 1px solid #ddd !important; padding: 4px !important; }
        
        /* Ocultar elementos específicos dentro del área de impresión que no queremos */
        .no-print, .btn, .card-header, .row.mb-4, .col-recaudar, .check-recaudar {
            display: none !important;
            visibility: hidden !important;
        }

        body { background: white !important; }
    }
</style>

<body>
    <div class="container-fluid mt-4 mb-5" id="area-impresion">
        <!-- Encabezado de Impresión -->
        <?= generarEncabezadoImpresion('REPORTE DE CAJAS (DINERO PENDIENTE)', $fecha_inicio, $fecha_fin) ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="card shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <h3 class="mb-0 m-0" style="font-size: 1.4rem;">
                            <i class="fas fa-cash-register mr-2"></i> REPORTE DE CAJAS (DINERO PENDIENTE)
                        </h3>
                        <button onclick="window.print()" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-print"></i> IMPRIMIR REPORTE
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4 no-print">
                            <div class="col-md-3">
                                <label for="fecha_inicio">Fecha Inicio:</label>
                                <input type="date" id="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin">Fecha Fin:</label>
                                <input type="date" id="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
                            </div>
                            <?php if ($rol_usuario === 'PROPIETARIO' || $rol_usuario === 'ADMINISTRADOR'): ?>
                                <div class="col-md-3">
                                    <label for="usuarioID">Filtrar por Usuario:</label>
                                    <select id="usuarioID" class="form-control">
                                        <option value="">Todos los usuarios</option>
                                        <?php foreach ($usuarios_mov as $usr): ?>
                                            <option value="<?= $usr['usuarioID'] ?>" <?= $usuarioID_filtro == $usr['usuarioID'] ? 'selected' : '' ?>>
                                                <?= $usr['usuario'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-3 d-flex align-items-end">
                                <button onclick="filtrar()" class="btn btn-secondary w-100"><i
                                        class="fas fa-filter"></i> Filtrar</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped tabla-turno border-bottom mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">Fecha Apertura</th>
                                        <th class="text-center">Usuario</th>
                                        <?php foreach ($formas_pago as $forma_pago): ?>
                                            <th class="text-center"><?= $forma_pago['tipo'] ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-center text-info">BAÑOS</th>
                                        <th class="text-end">Total</th>
                                        <?php if ($rol_usuario !== 'RECEPCIONISTA'): ?>
                                            <th width="4%"></th>
                                            <th width="4%" class="text-center"></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $hayRegistros = false;
                                    foreach ($vista_semanal as $fecha => $datos): ?>
                                        <?php if (empty($datos['movimientos'])): ?>
                                            <tr>
                                                <td colspan="<?= ($rol_usuario !== 'RECEPCIONISTA' ? 3 : 2) + count($formas_pago) + 1 ?>"
                                                    class="text-center text-muted fst-italic">
                                                    <?= date('d/m/Y', strtotime($fecha)) ?> - Sin pendientes
                                                </td>
                                            </tr>
                                        <?php else:
                                            $hayRegistros = true;
                                            foreach ($datos['movimientos'] as $movimiento): ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <?= date('d/m/Y', strtotime($movimiento['fecha_apertura'])) ?><br>
                                                        <small
                                                            class="text-muted"><?= date('H:i', strtotime($movimiento['fecha_apertura'])) ?></small>
                                                    </td>
                                                    <td class="text-center align-middle"><?= $movimiento['usuario'] ?></td>

                                                    <?php
                                                    $total_fila = 0;
                                                    foreach ($formas_pago as $forma_pago):
                                                        $monto = $movimiento['saldos'][$forma_pago['tipo']] ?? 0;
                                                        $total_fila += $monto;
                                                        $suma_footer_formas[$forma_pago['tipo']] += $monto;
                                                        ?>
                                                        <td class="text-center align-middle">Bs. <?= number_format($monto, 2) ?></td>
                                                    <?php endforeach; ?>

                                                    <td class="text-center align-middle text-info fw-bold">
                                                        Bs. <?= number_format($movimiento['saldo_bano'], 2) ?>
                                                    </td>

                                                    <?php 
                                                    $total_fila += $movimiento['saldo_bano'];
                                                    $suma_footer_total_general += $total_fila; 
                                                    ?>
                                                    <td class="text-end align-middle fw-bold">Bs.
                                                        <?= number_format($total_fila, 2) ?>
                                                    </td>

                                                    <?php if ($rol_usuario !== 'RECEPCIONISTA'): ?>
                                                        <td class="text-center align-middle" style="width: 40px; border-left: 1px solid #dee2e6;">
                                                            <button class="btn btn-sm btn-outline-secondary border-0"
                                                                onclick="verDetalleCaja(<?= $movimiento['cajaID'] ?>)"
                                                                title="Auditar Movimientos">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </td>
                                                        <td class="text-center align-middle col-recaudar" style="width: 40px; border-left: 1px solid #dee2e6;">
                                                                <input type="checkbox" class="check-recaudar form-check-input m-0 d-block mx-auto"
                                                                style="width: 18px; height: 18px; cursor: pointer; border: 1px solid #adb5bd;"
                                                                data-cajaid="<?= $movimiento['cajaID'] ?>"
                                                                data-monto="<?= array_sum($movimiento['saldos']) + $movimiento['saldo_bano'] ?>"
                                                                onclick="actualizarTotal()">
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>

                                <?php if ($hayRegistros): ?>
                                    <tfoot class="bg-light border-top">
                                        <tr>
                                            <th colspan="2" class="text-right align-middle text-muted" style="text-align: right;">TOTAL GENERAL:</th>
                                            <?php foreach ($formas_pago as $forma_pago): ?>
                                                <th class="text-center">
                                                    <?= number_format($suma_footer_formas[$forma_pago['tipo']], 2) ?> Bs.
                                                </th>
                                            <?php endforeach; ?>
                                            <th class="text-center text-info">
                                                <?php 
                                                $total_banos_footer = 0;
                                                foreach($vista_semanal as $d) foreach($d['movimientos'] as $m) $total_banos_footer += $m['saldo_bano'];
                                                echo number_format($total_banos_footer, 2);
                                                ?> Bs.
                                            </th>
                                            <th class="text-end" style="font-size: 1.1rem; border-left: 1px solid #dee2e6;">
                                                <?= number_format($suma_footer_total_general, 2) ?> Bs.
                                            </th>
                                            <?php if ($rol_usuario !== 'RECEPCIONISTA'): ?>
                                                <th style="border-left: 1px solid #dee2e6;"></th>
                                                <th class="col-recaudar" style="border-left: 1px solid #dee2e6;"></th>
                                            <?php endif; ?>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="recaudacion-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <span class="h5 mb-0">TOTAL A RECOGER: </span>
                <span id="txt-total-recaudar" class="h4 mb-0 fw-bold text-success">Bs. 0.00</span>
                <span class="ms-3 text-muted" style="color: white !important;" id="txt-count-recaudar">0 turnos
                    seleccionados</span>
            </div>
            <button class="btn btn-success btn-lg fw-bold" id="btn-confirmar-recaudacion"
                onclick="procesarRecaudacion()">
                <i class="fas fa-hand-holding-usd"></i> CONFIRMAR ENTREGA DE DINERO
            </button>
        </div>
    </div>

    <!-- Modal Auditoría de Turno -->
    <div class="modal fade" id="modalDetalleCaja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">AUDITORÍA DE MOVIMIENTOS DEL TURNO</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background:none; border:none; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body" id="modalDetalleContenido">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mt-2 text-muted">Cargando desglose financiero...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CERRAR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación (Bootstrap Estilo Card) -->
    <div class="modal fade" id="modalConfirmarRecaudacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-sm">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark">CONFIRMAR RECAUDACIÓN</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background:none; border:none; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body py-3">
                    <div class="card border-0 bg-light mb-0">
                        <div class="card-body p-3">
                            <p class="text-dark mb-0 fs-5" id="txtConfirmarCuerpo">
                                ¿Confirma que ha recibido físicamente el dinero de los turnos seleccionados?
                            </p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="button" class="btn btn-primary px-4 fw-bold" id="btnAceptarRecaudacion">CONFIRMAR ENTREGA</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Mensaje (Bootstrap) -->
    <div class="modal fade" id="modalMensajeApp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 shadow-lg">
                <div id="modalMensajeHeader" class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalMensajeTitulo"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="background:none; border:none; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <div id="modalMensajeIcono" class="mb-3" style="font-size: 3.5rem;"></div>
                    <p id="modalMensajeTexto" class="fs-5 mb-0 px-3"></p>
                </div>
                <div class="modal-footer border-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">ACEPTAR</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modalAuditoria = new bootstrap.Modal(document.getElementById('modalDetalleCaja'));
        const modalMensaje = new bootstrap.Modal(document.getElementById('modalMensajeApp'));
        const modalConfirmar = new bootstrap.Modal(document.getElementById('modalConfirmarRecaudacion'));

        function mostrarMensaje(titulo, texto, tipo = 'success') {
            const header = document.getElementById('modalMensajeHeader');
            const tituloEl = document.getElementById('modalMensajeTitulo');
            const textoEl = document.getElementById('modalMensajeTexto');
            const iconoEl = document.getElementById('modalMensajeIcono');

            tituloEl.innerText = titulo;
            textoEl.innerText = texto;

            if (tipo === 'success') {
                header.className = 'modal-header bg-success text-white py-3';
                iconoEl.innerHTML = '<i class="fas fa-check-circle text-success"></i>';
            } else {
                header.className = 'modal-header bg-danger text-white py-3';
                iconoEl.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i>';
            }

            modalMensaje.show();
        }

        function verDetalleCaja(cajaID) {
            const contenedor = document.getElementById('modalDetalleContenido');
            contenedor.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Cargando desglose financiero...</p></div>';
            modalAuditoria.show();

            fetch('ajax_detalle_caja.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cajaID=' + cajaID
            })
                .then(r => r.text())
                .then(html => {
                    contenedor.innerHTML = html;
                })
                .catch(err => {
                    contenedor.innerHTML = '<div class="alert alert-danger">Error de conexión al cargar los detalles.</div>';
                });
        }

        function actualizarTotal() {
            let total = 0;
            let count = 0;
            document.querySelectorAll('.check-recaudar:checked').forEach(chk => {
                total += parseFloat(chk.dataset.monto);
                count++;
            });

            const bar = document.getElementById('recaudacion-bar');
            if (count > 0) {
                bar.style.display = 'block';
                document.getElementById('txt-total-recaudar').innerText = 'Bs. ' + total.toLocaleString('es-BO', { minimumFractionDigits: 2 });
                document.getElementById('txt-count-recaudar').innerText = count + ' turnos seleccionados';
            } else {
                bar.style.display = 'none';
            }
        }

        function procesarRecaudacion() {
            const seleccionados = Array.from(document.querySelectorAll('.check-recaudar:checked'));
            const ids = seleccionados.map(chk => chk.dataset.cajaid);
            
            // Extraer fechas únicas
            const fechasUnicas = [...new Set(seleccionados.map(chk => {
                const fila = chk.closest('tr');
                return fila.cells[0].innerText.split(' ')[0];
            }))];

            // Ordenar fechas
            fechasUnicas.sort((a, b) => {
                const [da, ma, ya] = a.split('/');
                const [db, mb, yb] = b.split('/');
                return new Date(ya, ma - 1, da) - new Date(yb, mb - 1, db);
            });

            const listaFechas = fechasUnicas.join('<br>');

            document.getElementById('txtConfirmarCuerpo').innerHTML = `
                ¿Confirma que ha recibido el dinero de los <b>${ids.length}</b> turnos seleccionados?<br><br>
                <div class="text-center fw-bold">
                    ${listaFechas}
                </div>
            `;
            
            // Asignar el evento al botón de aceptar del modal
            document.getElementById('btnAceptarRecaudacion').onclick = function() {
                modalConfirmar.hide();
                ejecutarRecaudacion(ids);
            };

            modalConfirmar.show();
        }

        function ejecutarRecaudacion(ids) {
            const btn = document.getElementById('btn-confirmar-recaudacion');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> PROCESANDO...';

            fetch('ajax_recaudar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cajaIDs: ids })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'SUCCESS') {
                        mostrarMensaje('¡Éxito!', data.message, 'success');
                        setTimeout(() => { location.reload(); }, 2000);
                    } else {
                        mostrarMensaje('Error', data.message, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-hand-holding-usd"></i> CONFIRMAR ENTREGA DE DINERO';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarMensaje('Fallo Crítico', 'Error en la conexión con el servidor.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-hand-holding-usd"></i> CONFIRMAR ENTREGA DE DINERO';
                });
        }

        function filtrar() {
            const params = new URLSearchParams();
            params.set('fecha_inicio', document.getElementById('fecha_inicio').value);
            params.set('fecha_fin', document.getElementById('fecha_fin').value);

            const usuarioID = document.getElementById('usuarioID');
            if (usuarioID) {
                params.set('usuarioID', usuarioID.value);
            }
            window.location.href = '?' + params.toString();
        }
    </script>
</body>

</html>