<?php
session_start();
require_once("../../conexion.php");

// Procesar acciones (POST) - MOVIDO AL INICIO PARA EVITAR "HEADERS ALREADY SENT"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;
    $empresaID = $_SESSION['empresaID'];

    if ($accion === 'guardar') {
        $nombre = strtoupper(trim($_POST['nombre']));
        $precio = (float)$_POST['precio'];
        $descripcion = trim($_POST['descripcion']);
        $tipohabitacionID = $_POST['tipohabitacionID'] ?? null;

        if ($tipohabitacionID) {
            $sql = "UPDATE tipo_habitaciones SET nombre = ?, precio = ?, descripcion = ?, _fec_modificacion = NOW(), _usuario = ? 
                    WHERE tipohabitacionID = ? AND empresaID = ?";
            $db->ejecutar($sql, [$nombre, $precio, $descripcion, $usuarioID, $tipohabitacionID, $empresaID]);
        } else {
            $sql = "INSERT INTO tipo_habitaciones (nombre, precio, descripcion, empresaID, _fec_insercion, _usuario, _estado) 
                    VALUES (?, ?, ?, ?, NOW(), ?, 'A')";
            $db->ejecutar($sql, [$nombre, $precio, $descripcion, $empresaID, $usuarioID]);
        }
    } elseif ($accion === 'eliminar') {
        $tipohabitacionID = $_POST['tipohabitacionID'];
        $sql = "UPDATE tipo_habitaciones SET _estado = 'X', _fec_modificacion = NOW(), _usuario = ? 
                WHERE tipohabitacionID = ? AND empresaID = ?";
        $db->ejecutar($sql, [$usuarioID, $tipohabitacionID, $empresaID]);
    }
    header("Location: tipos.php");
    exit();
}

require_once("../../libreria_menu.php");

// Proteger acceso: Solo ADMINISTRADOR y PROPIETARIO
if (!isset($_SESSION['sesion_rol']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}

$empresaID = $_SESSION['empresaID'];

// Obtener listado
$sql = "SELECT * FROM tipo_habitaciones WHERE _estado <> 'X' AND empresaID = ? ORDER BY nombre ASC";
$tipos = $db->obtenerTodo($sql, [$empresaID]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tipos de Habitaciones - Sistema</title>
    <style>
        thead { color: black; background: #b5b5b5; }
        .card { margin: 20px; }
        .monto { font-weight: bold; color: #2c3e50; }
    </style>
</head>
<body>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">TIPOS DE HABITACIONES Y PRECIOS</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> NUEVO TIPO
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Categoría / Nombre</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Precio Sugerido</th>
                                    <th width="15%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tipos as $t): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($t['nombre']) ?></td>
                                    <td><small><?= $t['descripcion'] ? htmlspecialchars($t['descripcion']) : '' ?></small></td>
                                    <td class="text-end monto">Bs. <?= number_format($t['precio'], 2) ?></td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-4">
                                            <button style="background:none; border:none; color:#17a2b8; padding:0; cursor:pointer;" title="Modificar" 
                                                onclick="editarTipo(<?= $t['tipohabitacionID'] ?>, '<?= addslashes($t['nombre']) ?>', <?= $t['precio'] ?>, '<?= addslashes($t['descripcion']) ?>')">
                                                <i class="fas fa-pencil-alt fa-lg"></i>
                                            </button>
                                            <button style="background:none; border:none; color:#dc3545; padding:0; cursor:pointer;" title="Eliminar" onclick="eliminarTipo(<?= $t['tipohabitacionID'] ?>, '<?= addslashes($t['nombre']) ?>')">
                                                <i class="fas fa-trash-alt fa-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($tipos)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">No hay tipos registrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalTipo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Tipo de Habitación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="tipohabitacionID" id="tipohabitacionID">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre de la Categoría:</label>
                            <input type="text" name="nombre" id="txtNombre" class="form-control" required placeholder="Ej: MATRIMONIAL" onkeyup="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Precio Base (Bs.):</label>
                            <input type="number" step="0.50" name="precio" id="txtPrecio" class="form-control" required placeholder="0.00">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción / Detalles:</label>
                            <textarea name="descripcion" id="txtDescripcion" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Tipo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="formEliminar" method="post" style="display:none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="tipohabitacionID" id="delID">
    </form>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalTipo'));

        function abrirModal() {
            document.getElementById('modalTitle').innerText = 'Nuevo Tipo de Habitación';
            document.getElementById('tipohabitacionID').value = '';
            document.getElementById('txtNombre').value = '';
            document.getElementById('txtPrecio').value = '';
            document.getElementById('txtDescripcion').value = '';
            modal.show();
        }

        function editarTipo(id, nombre, precio, desc) {
            document.getElementById('modalTitle').innerText = 'Editar Tipo';
            document.getElementById('tipohabitacionID').value = id;
            document.getElementById('txtNombre').value = nombre;
            document.getElementById('txtPrecio').value = precio;
            document.getElementById('txtDescripcion').value = desc;
            modal.show();
        }

        function eliminarTipo(id, nombre) {
            if (confirm('¿Eliminar el tipo "' + nombre + '"?')) {
                document.getElementById('delID').value = id;
                document.getElementById('formEliminar').submit();
            }
        }
    </script>
</body>
</html>
