<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
require_once("utils/hospedajes_utilidades.php");

// Consulta para obtener los datos de los hospedajes de los últimos 2 días
$sql = "SELECT usu.usuario,h.hospedajeID,h.estado as estado,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ', c.apellido1, apellido2, c.nombres) SEPARATOR ', ') as clientes,
        h.checkin,h.checkout,r.numero AS habitacion_numero, h.monto, h.cajaID,
        GROUP_CONCAT(DISTINCT ip.formapagoID SEPARATOR ', ') as formapagoIDs
        FROM hospedajes h
        JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
        JOIN clientes c ON hc.clienteID = c.clienteID
        JOIN usuarios usu ON usu.usuarioID=h._usuario
        JOIN habitaciones r ON h.habitacionID = r.habitacionID
        LEFT JOIN ingresos i ON h.ingresoID = i.ingresoID
        LEFT JOIN ingreso_pagos ip ON i.ingresoID = ip.ingresoID
        WHERE h._estado <> 'X'
        AND hc._estado <> 'X'
        AND c._estado <> 'X'
        AND usu._estado<> 'x'
        AND h.empresaID = ?
        AND h.checkin >= DATE_SUB('" . date('Y-m-d H:i:s') . "', INTERVAL 3 DAY)
        GROUP BY h.hospedajeID
        ORDER BY h.hospedajeID DESC";

$caja_abierta_id = $_SESSION['caja_abierta_id'] ?? null;
$rs = $db->obtenerTodo($sql, [$_SESSION['empresaID']]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Hospedajes</title>
    <link rel="stylesheet" href="utils/hospedajes_estilos.css">
</head>

<body>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0">GESTIÓN HOSPEDAJES</h3>
        </div>

        <div class="card-body">
            <div class="form-group row align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold">Nombre:</label>
                    <input type="text" id="buscarNombres" class="form-control form-control-sm" placeholder="Buscar...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Apellidos:</label>
                    <input type="text" id="buscarApellidos" class="form-control form-control-sm"
                        placeholder="Buscar...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">C.I.:</label>
                    <input type="text" id="buscarCI" class="form-control form-control-sm" placeholder="Buscar...">
                </div>
                <div class="col-md-3">
                    <button type="button" id="botonBuscar" class="btn btn-primary btn-sm w-100 fw-bold">
                        <i class="fas fa-search"></i> BUSCAR
                    </button>
                </div>
            </div>

            <div id="mensaje"></div>
            <div class="table-responsive mt-3">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Usuario</th>
                            <th scope="col">Clientes</th>
                            <th scope="col">Fecha Ingreso</th>
                            <th scope="col">Fecha Salida</th>
                            <th scope="col">Habitación</th>
                            <th scope="col">Monto</th>
                            <th scope="col">Formas de Pago</th>
                            <th scope="col">Estado</th>
                            <th colspan="2" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs): ?>
                            <?php foreach ($rs as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['usuario']) ?></td>
                                    <td><?= htmlspecialchars($fila['clientes']) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($fila['checkin'])) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($fila['checkout'])) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($fila['habitacion_numero']) ?></td>
                                    <td class="text-center">Bs. <?= number_format($fila['monto'], 2) ?></td>
                                    <td class="text-center"><?= obtenerFormasPago($fila['formapagoIDs']) ?></td>
                                    <td class="text-center"><?= htmlspecialchars($fila['estado']) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-4">
                                            <form name="formModif<?= $fila['hospedajeID'] ?>" method="post"
                                                action="hospedaje_modificar.php" style="display:inline;">
                                                <input type="hidden" name="hospedajeID" value="<?= $fila['hospedajeID'] ?>">
                                                <input type="hidden" name="auth" value="hospedajes.php">
                                                <button type="submit"
                                                    style="background:none; border:none; color:#0d6efd; padding:0; cursor:pointer;"
                                                    title="Modificar">
                                                    <i class="fas fa-pencil-alt fa-lg"></i>
                                                </button>
                                            </form>
                                            <button type="button"
                                                style="background:none; border:none; color:#dc3545; padding:0; cursor:pointer;"
                                                onclick="eliminarHospedaje(<?= $fila['hospedajeID'] ?>, '<?= $fila['habitacion_numero'] ?>', <?= $fila['cajaID'] ?>)"
                                                title="Eliminar">
                                                <i class="fas fa-trash-alt fa-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <form id="formEliminar" method="post" action="hospedaje_eliminar.php">
        <input type="hidden" name="hospedajeID" id="eliminarID">
        <input type="hidden" name="motivo" id="eliminarMotivo">
        <input type="hidden" name="auth" value="hospedajes.php">
    </form>

    <!-- MODAL PARA ELIMINACIÓN -->
    <div class="modal fade" id="modalEliminarHospedaje" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"> <!-- Movido arriba (quitando modal-dialog-centered) -->
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt"></i> ELIMINAR HOSPEDAJE</h5>
                </div>
                <div class="modal-body">
                    <p class="text-black lead text-center">¿Está seguro de que desea eliminar el hospedaje de la <br>
                        <strong class="text-danger">Habitación <span id="numHabEliminar"></span></strong>?
                    </p>
                    <p class="text-muted small text-center">Esta acción enviará la habitación a <b>LIMPIEZA</b>.</p>
                    <label class="form-label fw-bold small text-black">Motivo de la eliminación (Obligatorio):</label>
                    <textarea class="form-control" id="txtMotivoEliminar" rows="3"
                        placeholder="Ej: Error en el registro, cancelación por parte del cliente..."
                        onkeyup="this.value=this.value.toUpperCase()"></textarea>
                    <div id="errorEliminar" class="text-danger small fw-bold mt-2" style="display: none;">
                        <i class="fas fa-exclamation-circle"></i> Por favor, ingrese un motivo válido (mínimo 5
                        caracteres).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()">Confirmar
                        Eliminación</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Acceso Denegado -->
    <div class="modal fade" id="modalAccesoDenegado" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-light">
                    <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-ban"></i> ACCESO DENEGADO</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center py-4">
                    <i class="fas fa-hand-paper fa-3x text-danger mb-3"></i>
                    <p class="mb-0 fw-bold">¡Acción Prohibida!</p>
                    <p class="text-muted">No puede eliminar registros que no pertenecen a su turno actual.</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"
                        data-bs-dismiss="modal">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let hospedajeIDEliminar = null;
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarHospedaje'));

        const CAJA_ABIERTA_ID = <?= json_encode($caja_abierta_id) ?>;
        const modalAccesoDenegado = new bootstrap.Modal(document.getElementById('modalAccesoDenegado'));

        function eliminarHospedaje(hospedajeID, numeroHab, registroCajaID) {
            // BLOQUEO PREVENTIVO FRONTEND
            if (registroCajaID != CAJA_ABIERTA_ID) {
                modalAccesoDenegado.show();
                return;
            }

            hospedajeIDEliminar = hospedajeID;
            document.getElementById('numHabEliminar').innerText = numeroHab;
            document.getElementById('txtMotivoEliminar').value = '';
            document.getElementById('errorEliminar').style.display = 'none';
            modalEliminar.show();
        }

        function confirmarEliminacion() {
            const motivo = document.getElementById('txtMotivoEliminar').value;
            const errorDiv = document.getElementById('errorEliminar');

            if (motivo.trim().length < 5) {
                errorDiv.style.display = 'block';
                return;
            }

            errorDiv.style.display = 'none';
            document.getElementById('eliminarID').value = hospedajeIDEliminar;
            document.getElementById('eliminarMotivo').value = motivo;
            document.getElementById('formEliminar').submit();
        }
    </script>

    <script src="js/hospedajes_busqueda.js"></script>
</body>

</html>