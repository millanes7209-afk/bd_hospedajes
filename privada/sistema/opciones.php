<?php
session_start();
require_once("../../conexion.php");
require_once("libreria_sistema.php");

// Proteger acceso: Solo ADMINISTRADOR (Sistema Global)
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] !== 'ADMINISTRADOR') {
    header("Location: ../../index.php");
    exit();
}

// Procesar acciones (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;

    if ($accion === 'guardar') {
        $opcion = strtoupper(trim($_POST['opcion']));
        $contenido = trim($_POST['contenido']);
        $grupoID = (int) $_POST['grupoID'];
        $funcionalidadID = (int) $_POST['funcionalidadID'];
        $orden = (int) $_POST['orden'];
        $opcionID = $_POST['opcionID'] ?? null;

        if ($opcionID) {
            // Actualizar
            $sql = "UPDATE opciones SET grupoID = ?, funcionalidadID = ?, opcion = ?, contenido = ?, orden = ?, _fec_modificacion = NOW(), _usuario = ? WHERE opcionID = ?";
            $db->ejecutar($sql, [$grupoID, $funcionalidadID, $opcion, $contenido, $orden, $usuarioID, $opcionID]);
        } else {
            // Insertar
            $sql = "INSERT INTO opciones (grupoID, funcionalidadID, opcion, contenido, orden, _fec_insercion, _usuario, _estado) VALUES (?, ?, ?, ?, ?, NOW(), ?, 'A')";
            $db->ejecutar($sql, [$grupoID, $funcionalidadID, $opcion, $contenido, $orden, $usuarioID]);

            // --- RECUPERACIÓN DE LÓGICA DE TRIGGER ELIMINADO ---
            $nuevaOpcionID = $db->ultimoInsertId();
            if ($nuevaOpcionID) {
                // Verificar si ya existe el acceso para el Administrador (Rol 1)
                $existe = $db->obtenerFila("SELECT 1 FROM accesos WHERE rolID = 1 AND opcionID = ?", [$nuevaOpcionID]);
                if (!$existe) {
                    $sqlAcceso = "INSERT INTO accesos (rolID, opcionID, _fec_insercion, _usuario, _estado) VALUES (1, ?, NOW(), ?, 'A')";
                    $db->ejecutar($sqlAcceso, [$nuevaOpcionID, $usuarioID]);
                }
            }
        }
    } elseif ($accion === 'eliminar') {
        $opcionID = $_POST['opcionID'];
        $sql = "UPDATE opciones SET _estado = 'X', _fec_modificacion = NOW(), _usuario = ? WHERE opcionID = ?";
        $db->ejecutar($sql, [$usuarioID, $opcionID]);
    }
    header("Location: opciones.php");
    exit();
}

// Obtener listado de grupos para el select
$grupos_select = $db->obtenerTodo("SELECT grupoID, grupo FROM grupos WHERE _estado <> 'X' ORDER BY grupo");

// Obtener listado de funcionalidades para el select
$funcionalidades_select = $db->obtenerTodo("SELECT funcionalidadID, nombre FROM funcionalidades WHERE _estado <> 'X' ORDER BY nombre");

// Obtener listado de opciones
$sql = "SELECT o.*, g.grupo, f.nombre as funcionalidad
        FROM opciones o 
        INNER JOIN grupos g ON o.grupoID = g.grupoID
        LEFT JOIN funcionalidades f ON o.funcionalidadID = f.funcionalidadID
        WHERE o._estado <> 'X' 
        ORDER BY g.grupo, o.orden ASC";
$opciones = $db->obtenerTodo($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Configuración de Opciones - Sistema</title>
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
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">GESTIÓN DE OPCIONES (TABS)</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> NUEVA OPCIÓN
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Grupo</th>
                                    <th>Nombre Opción (Tab)</th>
                                    <th>Archivo (Contenido)</th>
                                    <th>Módulo / Funcionalidad</th>
                                    <th class="text-center">Orden</th>
                                    <th width="15%" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($opciones as $o): ?>
                                    <tr>
                                        <td><span class=""><?= htmlspecialchars($o['grupo']) ?></span></td>
                                        <td class="fw-bold"><?= htmlspecialchars($o['opcion']) ?></td>
                                        <td><code><?= htmlspecialchars($o['contenido']) ?></code></td>
                                        <td><span
                                                class="badge bg-light text-dark border"><?= $o['funcionalidad'] ? htmlspecialchars($o['funcionalidad']) : 'BÁSICO' ?></span>
                                        </td>
                                        <td class="text-center"><?= $o['orden'] ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm text-white"
                                                onclick="editarOpcion(<?= $o['opcionID'] ?>, <?= $o['grupoID'] ?>, '<?= addslashes($o['opcion']) ?>', '<?= addslashes($o['contenido']) ?>', <?= $o['orden'] ?>, <?= (int) $o['funcionalidadID'] ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm"
                                                onclick="eliminarOpcion(<?= $o['opcionID'] ?>, '<?= addslashes($o['opcion']) ?>')">
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
    <div class="modal fade" id="modalOpcion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nueva Opción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="opcionID" id="opcionID">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Grupo Perteneciente:</label>
                            <select name="grupoID" id="selGrupo" class="form-control" required>
                                <?php foreach ($grupos_select as $gs): ?>
                                    <option value="<?= $gs['grupoID'] ?>"><?= htmlspecialchars($gs['grupo']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Módulo / Funcionalidad:</label>
                            <select name="funcionalidadID" id="selFuncionalidad" class="form-control" required>
                                <?php foreach ($funcionalidades_select as $fs): ?>
                                    <option value="<?= $fs['funcionalidadID'] ?>"><?= htmlspecialchars($fs['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre de la Pestaña:</label>
                            <input type="text" name="opcion" id="txtOpcion" class="form-control" required
                                placeholder="Ej: MAPA" onkeyup="this.value = this.value.toUpperCase()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ruta del Archivo (.php):</label>
                            <input type="text" name="contenido" id="txtContenido" class="form-control" required
                                placeholder="pestaña.php o subfolder/page.php">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Orden de Aparición:</label>
                            <input type="number" name="orden" id="txtOrden" class="form-control" required value="10">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">¿Realmente desea eliminar la opción: <br><b id="delOpcionNombre"
                            class="text-danger fs-5"></b>?</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="button" id="btnConfirmarBorrado" class="btn btn-danger fw-bold">SÍ, ELIMINAR</button>
                </div>
            </div>
        </div>
    </div>

    <form id="formEliminar" method="post" style="display:none;">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="opcionID" id="delOpcionID">
    </form>

    <script>
        function abrirModal() {
            document.getElementById('modalTitle').innerText = 'Nueva Opción';
            document.getElementById('opcionID').value = '';
            document.getElementById('selGrupo').selectedIndex = 0;
            document.getElementById('selFuncionalidad').value = '1'; // Por defecto Operación Base
            document.getElementById('txtOpcion').value = '';
            document.getElementById('txtContenido').value = '';
            document.getElementById('txtOrden').value = '10';
            let modal = new bootstrap.Modal(document.getElementById('modalOpcion'));
            modal.show();
        }

        function editarOpcion(id, grupoId, nombre, archivo, orden, funcId) {
            document.getElementById('modalTitle').innerText = 'Editar Opción';
            document.getElementById('opcionID').value = id;
            document.getElementById('selGrupo').value = grupoId;
            document.getElementById('selFuncionalidad').value = funcId;
            document.getElementById('txtOpcion').value = nombre;
            document.getElementById('txtContenido').value = archivo;
            document.getElementById('txtOrden').value = orden;
            let modal = new bootstrap.Modal(document.getElementById('modalOpcion'));
            modal.show();
        }

        function eliminarOpcion(id, nombre) {
            document.getElementById('delOpcionID').value = id;
            document.getElementById('delOpcionNombre').innerText = nombre;
            let modalEliminar = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
            modalEliminar.show();
        }

        document.getElementById('btnConfirmarBorrado').addEventListener('click', function () {
            document.getElementById('formEliminar').submit();
        });
    </script>
</body>

</html>