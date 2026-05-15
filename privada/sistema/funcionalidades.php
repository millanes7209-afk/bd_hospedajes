<?php
require_once("libreria_sistema.php");

// Procesar Guardar Funcionalidad (Nuevo/Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_funcionalidad') {
    $funcionalidadID = $_POST['funcionalidadID'] ?? null;
    $nombre = strtoupper(trim($_POST['nombre']));
    $descripcion = trim($_POST['descripcion']);
    $usuarioID = $_SESSION['sesion_id_usuario'];

    if ($funcionalidadID) {
        $sql = "UPDATE funcionalidades SET nombre = ?, descripcion = ?, _fec_modificacion = NOW(), _usuario = ? WHERE funcionalidadID = ?";
        $db->ejecutar($sql, [$nombre, $descripcion, $usuarioID, $funcionalidadID]);
        $mensaje = ["tipo" => "info", "texto" => "Nivel de funcionalidad actualizado."];
    } else {
        $sql = "INSERT INTO funcionalidades (nombre, descripcion, _fec_insercion, _usuario, _estado) VALUES (?, ?, NOW(), ?, 'A')";
        $db->ejecutar($sql, [$nombre, $descripcion, $usuarioID]);
        $mensaje = ["tipo" => "success", "texto" => "Nuevo nivel de funcionalidad creado."];
    }
}

// Consultas
$funcionalidades = $db->obtenerTodo("SELECT * FROM funcionalidades WHERE _estado <> 'X' AND funcionalidadID <> 5 ORDER BY funcionalidadID ASC");
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Gestión de Niveles (Módulos)</h4>
        <button class="btn btn-success btn-sm fw-bold" onclick="abrirModalFuncionalidad()">
            <i class="fas fa-plus-circle me-1"></i> NUEVO NIVEL
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-<?php echo $mensaje['tipo']; ?> alert-dismissible fade show" role="alert">
                <?php echo $mensaje['texto']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre del Nivel / Paquete</th>
                        <th>Descripción</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($funcionalidades as $f): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?php echo $f['funcionalidadID']; ?></span></td>
                            <td class="fw-bold text-primary"><?php echo $f['nombre']; ?></td>
                            <td><?php echo $f['descripcion'] ?? 'Sin descripción'; ?></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-info text-white" onclick='editarFuncionalidad(<?= json_encode($f) ?>)'>
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Nueva/Editar Funcionalidad -->
<div class="modal fade" id="modalFuncionalidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_funcionalidad">
                <input type="hidden" name="funcionalidadID" id="f_funcionalidadID">
                <div class="modal-header">
                    <h5 class="modal-title" id="fModalTitle">Datos del Nivel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre del Nivel:</label>
                        <input type="text" name="nombre" id="f_nombre" class="form-control" required onkeyup="this.value=this.value.toUpperCase()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción / ¿Qué incluye?:</label>
                        <textarea name="descripcion" id="f_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Nivel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalFuncionalidad() {
    document.getElementById('fModalTitle').innerText = 'Nuevo Nivel de Funcionalidad';
    document.getElementById('f_funcionalidadID').value = '';
    document.getElementById('f_nombre').value = '';
    document.getElementById('f_descripcion').value = '';
    let modal = new bootstrap.Modal(document.getElementById('modalFuncionalidad'));
    modal.show();
}

function editarFuncionalidad(data) {
    document.getElementById('fModalTitle').innerText = 'Editar Nivel';
    document.getElementById('f_funcionalidadID').value = data.funcionalidadID;
    document.getElementById('f_nombre').value = data.nombre;
    document.getElementById('f_descripcion').value = data.descripcion || '';
    let modal = new bootstrap.Modal(document.getElementById('modalFuncionalidad'));
    modal.show();
}
</script>

</body>
</html>
