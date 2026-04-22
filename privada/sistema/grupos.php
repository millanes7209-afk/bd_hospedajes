<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Proteger acceso: Solo ADMINISTRADOR y PROPIETARIO
if (!isset($_SESSION['sesion_rol']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}

// Procesar acciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;

    if ($accion === 'guardar') {
        $grupo = strtoupper(trim($_POST['grupo']));
        $grupoID = $_POST['grupoID'] ?? null;

        if ($grupoID) {
            // Actualizar
            $sql = "UPDATE grupos SET grupo = ?, _fec_modificacion = NOW(), _usuario = ? WHERE grupoID = ?";
            $db->ejecutar($sql, [$grupo, $usuarioID, $grupoID]);
        } else {
            // Insertar
            $sql = "INSERT INTO grupos (grupo, _fec_insercion, _usuario, _estado) VALUES (?, NOW(), ?, 'A')";
            $db->ejecutar($sql, [$grupo, $usuarioID]);
        }
    } elseif ($accion === 'eliminar') {
        $grupoID = $_POST['grupoID'];
        $sql = "UPDATE grupos SET _estado = 'X', _fec_modificacion = NOW(), _usuario = ? WHERE grupoID = ?";
        $db->ejecutar($sql, [$usuarioID, $grupoID]);
    }
    header("Location: grupos.php");
    exit();
}

// Obtener listado
$sql = "SELECT * FROM grupos WHERE _estado <> 'X' ORDER BY grupoID ASC";
$grupos = $db->obtenerTodo($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Configuración de Grupos - Sistema</title>
    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }

        .card {
            margin: 20px;
        }

        .table-sm td,
        .table-sm th {
            padding: 0.3rem;
        }
    </style>
</head>

<body>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">GESTIÓN DE GRUPOS (MENÚ)</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> NUEVO GRUPO
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">ID</th>
                                    <th>Nombre del Grupo</th>
                                    <th width="15%" class="text-center">Estado</th>
                                    <th width="20%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grupos as $g): ?>
                                    <tr>
                                        <td class="text-center"><?= $g['grupoID'] ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($g['grupo']) ?></td>
                                        <td class="text-center">
                                            <span class="badge ">ACTIVO</span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm text-white"
                                                onclick="editarGrupo(<?= $g['grupoID'] ?>, '<?= addslashes($g['grupo']) ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="eliminarGrupo(<?= $g['grupoID'] ?>, '<?= addslashes($g['grupo']) ?>')">
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
        </div>
    </div>

    <!-- Modal para Agregar/Editar -->
    <div class="modal fade" id="modalGrupo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Grupo</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="border:none; background:none; font-size:1.5rem;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="grupoID" id="grupoID">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre del Grupo:</label>
                            <input type="text" name="grupo" id="txtGrupo" class="form-control" required
                                placeholder="Ej: CONFIGURACIÓN" onkeyup="this.value = this.value.toUpperCase()">
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

    <!-- Formulario oculto para eliminar -->
    <form id="formEliminar" method="post" style="display:none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="grupoID" id="delGrupoID">
    </form>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalGrupo'));

        function abrirModal() {
            document.getElementById('modalTitle').innerText = 'Nuevo Grupo';
            document.getElementById('grupoID').value = '';
            document.getElementById('txtGrupo').value = '';
            modal.show();
        }

        function editarGrupo(id, nombre) {
            document.getElementById('modalTitle').innerText = 'Editar Grupo';
            document.getElementById('grupoID').value = id;
            document.getElementById('txtGrupo').value = nombre;
            modal.show();
        }

        function eliminarGrupo(id, nombre) {
            if (confirm('¿Estás seguro de eliminar el grupo "' + nombre + '"?\nEsto ocultará todas sus opciones vinculadas.')) {
                document.getElementById('delGrupoID').value = id;
                document.getElementById('formEliminar').submit();
            }
        }
    </script>
</body>

</html>