<?php
session_start();
require_once("../../conexion.php");

// Procesar acciones (POST) - MOVIDO AL INICIO PARA EVITAR "HEADERS ALREADY SENT"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;
    $empresaID = $_SESSION['empresaID'];

    if ($accion === 'guardar') {
        $numero = trim($_POST['numero']);
        $tipohabitacionID = (int)$_POST['tipohabitacionID'];
        $descripcion = trim($_POST['descripcion']);
        $tv = isset($_POST['tv']) ? 1 : 0;
        $bano = isset($_POST['bano']) ? 1 : 0;
        $ventilador = isset($_POST['ventilador']) ? 1 : 0;
        $habitacionID = $_POST['habitacionID'] ?? null;

        if ($habitacionID) {
            $sql = "UPDATE habitaciones SET tipohabitacionID = ?, numero = ?, descripcion = ?, tv = ?, bano = ?, ventilador = ?, _fec_modificacion = NOW(), _usuario = ? 
                    WHERE habitacionID = ? AND empresaID = ?";
            $db->ejecutar($sql, [$tipohabitacionID, $numero, $descripcion, $tv, $bano, $ventilador, $usuarioID, $habitacionID, $empresaID]);
        } else {
            $sql = "INSERT INTO habitaciones (tipohabitacionID, empresaID, numero, estado, descripcion, tv, bano, ventilador, _fec_insercion, _usuario, _estado) 
                    VALUES (?, ?, ?, 'DISPONIBLE', ?, ?, ?, ?, NOW(), ?, 'A')";
            $db->ejecutar($sql, [$tipohabitacionID, $empresaID, $numero, $descripcion, $tv, $bano, $ventilador, $usuarioID]);
        }
    } elseif ($accion === 'eliminar') {
        $habitacionID = $_POST['habitacionID'];
        $sql = "UPDATE habitaciones SET _estado = 'X', _fec_modificacion = NOW(), _usuario = ? 
                WHERE habitacionID = ? AND empresaID = ?";
        $db->ejecutar($sql, [$usuarioID, $habitacionID, $empresaID]);
    }
    header("Location: habit_lista.php");
    exit();
}

require_once("../../libreria_menu.php");

// Proteger acceso: Solo ADMINISTRADOR y PROPIETARIO
if (!isset($_SESSION['sesion_rol']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}

$empresaID = $_SESSION['empresaID'];

// Obtener listado de tipos para el modal
$tipos_select = $db->obtenerTodo("SELECT tipohabitacionID, nombre FROM tipo_habitaciones WHERE _estado <> 'X' AND empresaID = ? ORDER BY nombre", [$empresaID]);

// Obtener listado de habitaciones
$sql = "SELECT h.*, th.nombre as tipo_nombre, th.precio
        FROM habitaciones h
        INNER JOIN tipo_habitaciones th ON h.tipohabitacionID = th.tipohabitacionID
        WHERE h._estado <> 'X' AND h.empresaID = ? 
        ORDER BY h.numero ASC";
$habitaciones = $db->obtenerTodo($sql, [$empresaID]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario de Habitaciones - Sistema</title>
    <style>
        thead { color: black; background: #b5b5b5; }
        .card { margin: 20px; }
        .feature-icon { margin-right: 10px; font-size: 0.85rem; }
    </style>
</head>
<body>
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">INVENTARIO DE HABITACIONES</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> AGREGAR HABITACIÓN
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th width="10%">NÚMERO</th>
                                    <th width="20%">TIPO / CATEGORÍA</th>
                                    <th>CARACTERÍSTICAS / EXTRAS</th>
                                    <th class="text-center">ESTADO ACTUAL</th>
                                    <th width="15%" class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($habitaciones as $h): ?>
                                <tr>
                                    <td class="text-center"><h5 class="mb-0 fw-bold"><?= htmlspecialchars($h['numero']) ?></h5></td>
                                    <td>
                                        <div><?= htmlspecialchars($h['tipo_nombre']) ?></div>
                                        <small class="text-muted">Precio: Bs. <?= number_format($h['precio'], 2) ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                            $features = [];
                                            if($h['tv']) $features[] = "TV";
                                            if($h['bano']) $features[] = "BAÑO";
                                            if($h['ventilador']) $features[] = "VENTILADOR";
                                            echo !empty($features) ? implode(", ", $features) : "Sin extras";
                                        ?>
                                        <div class="small mt-1 text-muted fst-italic"><?php echo htmlspecialchars((string)($h['descripcion'] ?? '')); ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="fw-bold fs-6"><?= $h['estado'] ?></span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php 
                                            // Limpiar descripción de saltos de línea para evitar errores de sintaxis en el JS onclick
                                            $desc_js = str_replace(["\r", "\n"], ["", " "], (string)($h['descripcion'] ?? ''));
                                        ?>
                                        <button class="btn btn-info btn-sm text-white px-2 fw-bold" 
                                            onclick="editarHabitacion(<?= $h['habitacionID'] ?>, <?= $h['tipohabitacionID'] ?>, '<?= addslashes($h['numero']) ?>', '<?= addslashes($desc_js) ?>', <?= $h['tv'] ?>, <?= $h['bano'] ?>, <?= $h['ventilador'] ?>)">
                                            <i class="fas fa-edit"></i> Modificar
                                        </button>
                                        <button class="btn btn-danger btn-sm px-2 fw-bold" onclick="eliminarHabitacion(<?= $h['habitacionID'] ?>, '<?= addslashes($h['numero']) ?>')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($habitaciones)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">No hay habitaciones registradas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalHabitacion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nueva Habitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="habitacionID" id="habitacionID">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Número de Hab.:</label>
                                <input type="text" name="numero" id="txtNumero" class="form-control" required placeholder="Ej: 101">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tipo / Categoría:</label>
                                <select name="tipohabitacionID" id="selTipo" class="form-control" required>
                                    <?php foreach($tipos_select as $ts): ?>
                                        <option value="<?= $ts['tipohabitacionID'] ?>"><?= htmlspecialchars($ts['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Equipamiento:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tv" id="chkTv">
                                    <label class="form-check-label" for="chkTv">TV Cable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="bano" id="chkBano">
                                    <label class="form-check-label" for="chkBano">Baño Privado</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="ventilador" id="chkVent">
                                    <label class="form-check-label" for="chkVent">Ventilador</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción Adicional:</label>
                            <textarea name="descripcion" id="txtDescripcion" class="form-control" rows="2" placeholder="Ej: 2 Camas simples..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="formEliminar" method="post" style="display:none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="habitacionID" id="delID">
    </form>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalHabitacion'));

        function abrirModal() {
            document.getElementById('modalTitle').innerText = 'Agregar Habitación';
            document.getElementById('habitacionID').value = '';
            document.getElementById('txtNumero').value = '';
            document.getElementById('txtDescripcion').value = '';
            document.getElementById('selTipo').selectedIndex = 0;
            document.getElementById('chkTv').checked = false;
            document.getElementById('chkBano').checked = false;
            document.getElementById('chkVent').checked = false;
            modal.show();
        }

        function editarHabitacion(id, tipoID, numero, desc, tv, bano, vent) {
            document.getElementById('modalTitle').innerText = 'Editar Habitación';
            document.getElementById('habitacionID').value = id;
            document.getElementById('txtNumero').value = numero;
            document.getElementById('txtDescripcion').value = desc;
            document.getElementById('selTipo').value = tipoID;
            document.getElementById('chkTv').checked = (tv == "1");
            document.getElementById('chkBano').checked = (bano == "1");
            document.getElementById('chkVent').checked = (vent == "1");
            modal.show();
        }

        function eliminarHabitacion(id, numero) {
            if (confirm('¿Eliminar la habitación N° ' + numero + '?')) {
                document.getElementById('delID').value = id;
                document.getElementById('formEliminar').submit();
            }
        }
    </script>
</body>
</html>
