<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
require_once("utils/hospedajes_utilidades.php");

// Consulta para obtener los datos de los hospedajes
$sql = "SELECT usu.usuario,h.hospedajeID,h.estado as estado,
        GROUP_CONCAT(DISTINCT CONCAT_WS(' ', c.apellido1, apellido2, c.nombres) SEPARATOR ', ') as clientes,
        h.checkin,h.checkout,r.numero AS habitacion_numero, h.monto,
        GROUP_CONCAT(DISTINCT m.formapagoID SEPARATOR ', ') as formapagoIDs
        FROM hospedajes h
        JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
        JOIN clientes c ON hc.clienteID = c.clienteID
        join usuarios usu ON usu.usuarioID=h._usuario
        JOIN habitaciones r ON h.habitacionID = r.habitacionID
        LEFT JOIN movimientos m ON h.hospedajeID = m.referenciaID AND m.categoria = 'HOSPEDAJE'
        WHERE h._estado <> 'X'
        AND hc._estado <> 'X'
        AND c._estado <> 'X'
        AND usu._estado<> 'x'
        AND h.empresaID = ?
        GROUP BY h.hospedajeID
        ORDER BY h.hospedajeID DESC";

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
            <?php if (in_array($_SESSION["sesion_rol"], ['ADMINISTRADOR', 'PROPIETARIO'])): ?>
                <a href="hospedajes_auditoria.php" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="fas fa-shield-alt"></i> PANEL AUDITORÍA
                </a>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-4">
                    <input type="text" id="buscarNombres" class="form-control" placeholder="Buscar Nombre">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buscarApellidos" class="form-control" placeholder="Buscar Apellidos">
                </div>
                <div class="col-md-4">
                    <input type="text" id="buscarCI" class="form-control" placeholder="Buscar CI">
                </div>
            </div>
            <div class="form-group row mt-2">
                <div class="col-md-12">
                    <button type="button" id="botonBuscar" class="btn btn-primary">Buscar</button>
                </div>
            </div>
            <div id="mensaje"></div>
            <div class="table-responsive">
                <table class="table table-striped">
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
                            <th scope="col"><img src='../../imagenes/modificar.gif' alt='Modificar'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg' alt='Eliminar'></th>
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
                                    <td style="text-align: center;"><?= htmlspecialchars($fila['habitacion_numero']) ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars($fila['monto']) ?></td>
                                    <td style="text-align: center;"><?= obtenerFormasPago($fila['formapagoIDs']) ?></td>
                                    <td style="text-align: center;"><?= htmlspecialchars($fila['estado']) ?></td>
                                    <td>
                                        <form name="formModif<?= $fila['hospedajeID'] ?>" method="post"
                                            action="hospedaje_modificar.php" style="display:inline;">
                                            <input type="hidden" name="hospedajeID" value="<?= $fila['hospedajeID'] ?>">
                                            <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger btn-accion"
                                            onclick="eliminarHospedaje(<?= $fila['hospedajeID'] ?>)">Eliminar</button>
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
    </form>

    <!-- MODAL PARA ELIMINACIÓN -->
    <div class="modal fade" id="modalEliminarHospedaje" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog"> <!-- Movido arriba (quitando modal-dialog-centered) -->
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt"></i> ELIMINAR HOSPEDAJE</h5>
                </div>
                <div class="modal-body">
                    <p class="text-black">¿Está seguro de que desea eliminar este hospedaje? Esta acción enviará la
                        habitación a <b>LIMPIEZA</b>.</p>
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

    <script>
        let hospedajeIDEliminar = null;
        const modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminarHospedaje'));

        function eliminarHospedaje(hospedajeID) {
            hospedajeIDEliminar = hospedajeID;
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