<?php
require_once("libreria_sistema.php");

// Procesar actualización de módulos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'guardar_modulos') {
    $empresaID = (int)$_POST['empresaID'];
    $modulos = $_POST['modulos'] ?? []; // IDs de funcionalidades seleccionadas

    // 1. Desactivar todos para esta empresa
    $db->ejecutar("UPDATE empresa_funcionalidades SET estado = 'INACTIVO', _fec_modificacion = NOW(), _usuario = ? WHERE empresaID = ?", [$_SESSION['sesion_id_usuario'], $empresaID]);

    // 2. Activar o Insertar los seleccionados
    foreach ($modulos as $fID) {
        $fID = (int)$fID;
        $existe = $db->obtenerFila("SELECT empresafuncionID FROM empresa_funcionalidades WHERE empresaID = ? AND funcionalidadID = ?", [$empresaID, $fID]);
        
        if ($existe) {
            $db->ejecutar("UPDATE empresa_funcionalidades SET estado = 'ACTIVO', _fec_modificacion = NOW(), _usuario = ? WHERE empresafuncionID = ?", [$_SESSION['sesion_id_usuario'], $existe['empresafuncionID']]);
        } else {
            $db->ejecutar("INSERT INTO empresa_funcionalidades (empresaID, funcionalidadID, fecha_activacion, estado, _fec_insercion, _usuario, _estado) 
                           VALUES (?, ?, NOW(), 'ACTIVO', NOW(), ?, 'A')", [$empresaID, $fID, $_SESSION['sesion_id_usuario']]);
        }
    }
    $mensaje = ["tipo" => "success", "texto" => "Módulos actualizados correctamente para la sucursal."];
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
        <h4 class="mb-0">Gestión de Sucursales y Módulos</h4>
        <a href="../empresas/empresa_nuevo.php" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle me-1"></i> Nueva Sucursal
        </a>
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
                                            echo '<span class="badge bg-primary me-1" title="'.$f['descripcion'].'">'.$f['nombre'].'</span>';
                                        }
                                    }
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <a href="../../validar1.php?id=<?php echo $e['empresaID']; ?>" class="btn btn-sm btn-success me-1" title="Entrar a esta sucursal">
                                    <i class="fas fa-door-open"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-primary" onclick='abrirModulos(<?php echo $e['empresaID']; ?>, "<?php echo $e['nombre']; ?>", <?php echo json_encode($activos); ?>)'>
                                    <i class="fas fa-boxes me-1"></i> Configurar
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
                    <p class="text-muted small mb-4">Selecciona los módulos que esta empresa tiene autorizados según su plan de pago.</p>
                    <?php foreach ($funcionalidades as $f): ?>
                        <div class="form-check mb-3 p-2 border rounded hover-bg-light">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="modulos[]" value="<?php echo $f['funcionalidadID']; ?>" id="f_<?php echo $f['funcionalidadID']; ?>">
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

<script src="../../bootstrap5/js/bootstrap.bundle.min.js"></script>
<script>
function abrirModulos(id, nombre, activos) {
    document.getElementById('m_empresaID').value = id;
    document.getElementById('m_nombre').innerText = nombre;
    
    // Resetear checks
    document.querySelectorAll('input[name="modulos[]"]').forEach(cb => {
        cb.checked = activos.includes(parseInt(cb.value));
    });
    
    new bootstrap.Modal(document.getElementById('modalModulos')).show();
}
</script>

<style>
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .badge { font-weight: 500; font-size: 0.75rem; }
</style>

</body>
</html>
