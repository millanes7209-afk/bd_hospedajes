<?php
require_once("libreria_sistema.php");

// Procesar actualización de módulos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_modulos') {
    $empresaID = (int) $_POST['empresaID'];
    $modulos = $_POST['modulos'] ?? []; // IDs de funcionalidades seleccionadas

    $ahora = date('Y-m-d H:i:s');
    // 1. Desactivar todos para esta empresa
    $db->ejecutar("UPDATE empresa_funcionalidades SET estado = 'INACTIVO', _fec_modificacion = ?, _usuario = ? WHERE empresaID = ?", [$ahora, $_SESSION['sesion_id_usuario'], $empresaID]);

    // 2. Activar o Insertar los seleccionados
    foreach ($modulos as $fID) {
        $fID = (int) $fID;
        $existe = $db->obtenerFila("SELECT empresafuncionID FROM empresa_funcionalidades WHERE empresaID = ? AND funcionalidadID = ?", [$empresaID, $fID]);

        if ($existe) {
            $db->ejecutar("UPDATE empresa_funcionalidades SET estado = 'ACTIVO', _fec_modificacion = ?, _usuario = ? WHERE empresafuncionID = ?", [$ahora, $_SESSION['sesion_id_usuario'], $existe['empresafuncionID']]);
        } else {
            $db->ejecutar("INSERT INTO empresa_funcionalidades (empresaID, funcionalidadID, fecha_activacion, estado, _fec_insercion, _usuario, _estado) 
                           VALUES (?, ?, ?, 'ACTIVO', ?, ?, 'A')", [$empresaID, $fID, $ahora, $ahora, $_SESSION['sesion_id_usuario']]);
        }
    }
    $mensaje = ["tipo" => "success", "texto" => "Módulos actualizados correctamente para la sucursal."];
}

// Procesar Guardar Sucursal (Nuevo/Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_sucursal') {
    $empresaID = $_POST['empresaID'] ?? null;
    $nombre = strtoupper(trim($_POST['nombre']));
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $ruc = trim($_POST['ruc']);
    $representante_legal = strtoupper(trim($_POST['representante_legal']));
    $color_primario = trim($_POST['color_primario']);
    $color_secundario = trim($_POST['color_secundario']);
    $usuarioID = $_SESSION['sesion_id_usuario'];

    // Manejo de Logo
    $logo_path = null;
    if (isset($_FILES['logo_agencia']) && $_FILES['logo_agencia']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['logo_agencia']['name'], PATHINFO_EXTENSION);
        $nombre_logo = "logo_" . time() . "_" . rand(100, 999) . "." . $ext;
        $ruta_destino = "../../img/" . $nombre_logo;

        // Crear carpeta si no existe (por si acaso)
        if (!is_dir("../../img/")) {
            mkdir("../../img/", 0777, true);
        }

        if (move_uploaded_file($_FILES['logo_agencia']['tmp_name'], $ruta_destino)) {
            $logo_path = $nombre_logo; // Solo guardamos el nombre del archivo
        }
    }

    $ahora = date('Y-m-d H:i:s');
    if ($empresaID) {
        // Editar
        if ($logo_path) {
            $sql = "UPDATE empresa SET nombre = ?, direccion = ?, telefono = ?, ruc = ?, representante_legal = ?, color_primario = ?, color_secundario = ?, logo_agencia = ?, _fec_modificacion = ?, _usuario = ? WHERE empresaID = ?";
            $db->ejecutar($sql, [$nombre, $direccion, $telefono, $ruc, $representante_legal, $color_primario, $color_secundario, $logo_path, $ahora, $usuarioID, $empresaID]);
        } else {
            $sql = "UPDATE empresa SET nombre = ?, direccion = ?, telefono = ?, ruc = ?, representante_legal = ?, color_primario = ?, color_secundario = ?, _fec_modificacion = ?, _usuario = ? WHERE empresaID = ?";
            $db->ejecutar($sql, [$nombre, $direccion, $telefono, $ruc, $representante_legal, $color_primario, $color_secundario, $ahora, $usuarioID, $empresaID]);
        }
        $mensaje = ["tipo" => "info", "texto" => "Datos de la empresa actualizados."];
    } else {
        // Nuevo
        $sql = "INSERT INTO empresa (nombre, direccion, telefono, ruc, representante_legal, color_primario, color_secundario, logo_agencia, _fec_insercion, _usuario, _estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')";
        $db->ejecutar($sql, [$nombre, $direccion, $telefono, $ruc, $representante_legal, $color_primario, $color_secundario, $logo_path, $ahora, $usuarioID]);
        $mensaje = ["tipo" => "success", "texto" => "Nueva empresa registrada con éxito."];
    }
}

// Procesar Eliminar Sucursal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'eliminar_sucursal') {
    $empresaID = $_POST['empresaID'];
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $ahora = date('Y-m-d H:i:s');
    $sql = "UPDATE empresa SET _estado = 'X', _fec_modificacion = ?, _usuario = ? WHERE empresaID = ?";
    $db->ejecutar($sql, [$ahora, $usuarioID, $empresaID]);
    $mensaje = ["tipo" => "danger", "texto" => "Sucursal eliminada del sistema."];
}

// Consultas
$empresas = $db->obtenerTodo("SELECT * FROM empresa WHERE _estado <> 'X' ORDER BY nombre");
$funcionalidades = $db->obtenerTodo("SELECT * FROM funcionalidades WHERE _estado <> 'X' AND funcionalidadID <> 5 ORDER BY nombre");

// Obtener mapa de módulos activos por empresa
$modulos_activos = [];
$res = $db->obtenerTodo("SELECT empresaID, funcionalidadID FROM empresa_funcionalidades WHERE estado = 'ACTIVO' AND _estado <> 'X'");
foreach ($res as $r) {
    $modulos_activos[$r['empresaID']][] = $r['funcionalidadID'];
}
?>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Panel Maestro y Empresas</h4>
        <div>
            <a href="../seguridad/licenciador.php" class="btn btn-warning btn-sm fw-bold text-dark me-2">
                <i class="fas fa-key me-1"></i> LICENCIAS (KILL-SWITCH)
            </a>
            <button class="btn btn-success btn-sm fw-bold" onclick="abrirModalSucursal()">
                <i class="fas fa-plus-circle me-1"></i> NUEVA EMPRESA
            </button>
        </div>
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
                        <th>Nombre de Sucursal</th>
                        <th>Ubicación / Contacto</th>
                        <th>Módulos Activos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empresas as $e): ?>
                        <tr>
                            <td><?php echo $e['empresaID']; ?></td>
                            <td class="fw-bold"><?php echo $e['nombre']; ?></td>
                            <td class="small text-muted">
                                <?php echo $e['direccion']; ?><br>
                                <?php echo $e['telefono']; ?>
                            </td>
                            <td>
                                <?php
                                $activos = $modulos_activos[$e['empresaID']] ?? [];
                                if (empty($activos)) {
                                    echo '<span class="badge bg-secondary">Sin módulos</span>';
                                } else {
                                    foreach ($funcionalidades as $f) {
                                        if (in_array($f['funcionalidadID'], $activos)) {
                                            echo '<span class="badge bg-primary me-1" title="' . $f['descripcion'] . '">' . $f['nombre'] . '</span>';
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="../../validar1.php?id=<?php echo $e['empresaID']; ?>"
                                    class="btn btn-sm btn-success me-1" title="Entrar a esta sucursal">
                                    <i class="fas fa-door-open"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-primary"
                                    onclick='abrirModulos(<?php echo $e['empresaID']; ?>, "<?php echo $e['nombre']; ?>", <?php echo json_encode($activos); ?>)'>
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <button class="btn btn-sm btn-info text-white"
                                    onclick='editarSucursal(<?= json_encode($e) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    onclick='eliminarSucursal(<?= $e['empresaID'] ?>, "<?= htmlspecialchars($e['nombre']) ?>")'>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Configuración de Módulos -->
<div class="modal fade" id="modalModulos" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="accion" value="guardar_modulos">
                <input type="hidden" name="empresaID" id="m_empresaID">
                <div class="modal-header">
                    <h5 class="modal-title">Módulos para: <span id="m_nombre"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-4">Selecciona los módulos que esta empresa tiene autorizados según su
                        plan de pago.</p>
                    <?php foreach ($funcionalidades as $f): ?>
                        <div class="form-check mb-3 p-2 border rounded hover-bg-light">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="modulos[]"
                                value="<?php echo $f['funcionalidadID']; ?>" id="f_<?php echo $f['funcionalidadID']; ?>">
                            <label class="form-check-label d-block" for="f_<?php echo $f['funcionalidadID']; ?>">
                                <div class="fw-bold"><?php echo $f['nombre']; ?></div>
                                <div class="small text-muted"><?php echo $f['descripcion']; ?></div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    function abrirModulos(id, nombre, activos) {
        document.getElementById('m_empresaID').value = id;
        document.getElementById('m_nombre').innerText = nombre;

        // Resetear checks
        document.querySelectorAll('input[name="modulos[]"]').forEach(cb => {
            cb.checked = activos.includes(parseInt(cb.value));
        });

        let modal = new bootstrap.Modal(document.getElementById('modalModulos'));
        modal.show();
    }

    function abrirModalSucursal() {
        document.getElementById('sucModalTitle').innerText = 'Nueva Sucursal';
        document.getElementById('suc_empresaID').value = '';
        document.getElementById('suc_nombre').value = '';
        document.getElementById('suc_direccion').value = '';
        document.getElementById('suc_telefono').value = '';
        document.getElementById('suc_ruc').value = '';
        document.getElementById('suc_representante').value = '';
        document.getElementById('suc_color1').value = '#1a1a1a';
        document.getElementById('suc_color2').value = '#00f2fe';
        let modal = new bootstrap.Modal(document.getElementById('modalSucursal'));
        modal.show();
    }

    function editarSucursal(data) {
        document.getElementById('sucModalTitle').innerText = 'Editar Sucursal';
        document.getElementById('suc_empresaID').value = data.empresaID;
        document.getElementById('suc_nombre').value = data.nombre;
        document.getElementById('suc_direccion').value = data.direccion;
        document.getElementById('suc_telefono').value = data.telefono;
        document.getElementById('suc_ruc').value = data.ruc || '';
        document.getElementById('suc_representante').value = data.representante_legal || '';
        document.getElementById('suc_color1').value = data.color_primario || '#1a1a1a';
        document.getElementById('suc_color2').value = data.color_secundario || '#00f2fe';
        let modal = new bootstrap.Modal(document.getElementById('modalSucursal'));
        modal.show();
    }

    function eliminarSucursal(id, nombre) {
        if (confirm("¿Realmente desea eliminar la sucursal: " + nombre + "?")) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.innerHTML = '<input type="hidden" name="accion" value="eliminar_sucursal"><input type="hidden" name="empresaID" value="' + id + '">';
            document.body.appendChild(f);
            f.submit();
        }
    }
</script>

<!-- Modal Nueva/Editar Sucursal -->
<div class="modal fade" id="modalSucursal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="guardar_sucursal">
                <input type="hidden" name="empresaID" id="suc_empresaID">
                <div class="modal-header">
                    <h5 class="modal-title" id="sucModalTitle">Datos de Sucursal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre Comercial:</label>
                            <input type="text" name="nombre" id="suc_nombre" class="form-control" required
                                onkeyup="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">NIT / RUC:</label>
                            <input type="text" name="ruc" id="suc_ruc" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Representante Legal:</label>
                            <input type="text" name="representante_legal" id="suc_representante" class="form-control"
                                onkeyup="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono/WhatsApp:</label>
                            <input type="text" name="telefono" id="suc_telefono" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Dirección Completa:</label>
                        <input type="text" name="direccion" id="suc_direccion" class="form-control" required>
                    </div>

                    <div class="row bg-light p-2 rounded mb-3">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold text-muted small">Color Primario:</label>
                            <input type="color" name="color_primario" id="suc_color1"
                                class="form-control form-control-color w-100" title="Elegir color primario">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small">Color Secundario:</label>
                            <input type="color" name="color_secundario" id="suc_color2"
                                class="form-control form-control-color w-100" title="Elegir color secundario">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Logo de la Agencia (Imagen):</label>
                        <input type="file" name="logo_agencia" id="suc_logo" class="form-control" accept="image/*">
                        <small class="text-muted">Si no seleccionas un archivo, se mantendrá el logo actual.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }
</style>

</body>

</html>