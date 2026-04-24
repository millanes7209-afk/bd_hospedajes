<?php
session_start();
require_once("../../conexion.php");

// 1. PROCESAR ACCIONES (POST) - Debe ir antes de cualquier salida de texto (libreria_menu)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;

    if ($accion === 'conceder') {
        $rolID = (int) $_POST['rolID'];
        $opcionID = (int) $_POST['opcionID'];

        $check = $db->obtenerFila("SELECT accesoID FROM accesos WHERE rolID = ? AND opcionID = ?", [$rolID, $opcionID]);

        if ($check) {
            $sql = "UPDATE accesos SET _estado = 'A', _fec_modificacion = NOW(), _usuario = ? WHERE accesoID = ?";
            $db->ejecutar($sql, [$usuarioID, $check['accesoID']]);
        } else {
            $sql = "INSERT INTO accesos (rolID, opcionID, _fec_insercion, _usuario, _estado) VALUES (?, ?, NOW(), ?, 'A')";
            $db->ejecutar($sql, [$rolID, $opcionID, $usuarioID]);
        }
    } elseif ($accion === 'revocar') {
        $accesoID = $_POST['accesoID'];
        $sql = "UPDATE accesos SET _estado = 'X', _fec_modificacion = NOW(), _usuario = ? WHERE accesoID = ?";
        $db->ejecutar($sql, [$usuarioID, $accesoID]);
    }
    header("Location: accesos.php");
    exit();
}

// 2. PROTECCIÓN DE ACCESO
if (!isset($_SESSION['sesion_rol']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    header("Location: ../../index.php");
    exit();
}

require_once("../../libreria_menu.php");

// 3. CONSULTAS PARA LA VISTA
// Filtrar el rol 1 (Administrador) ya que tiene trigger/permisos fijos
$roles_select = $db->obtenerTodo("SELECT rolID, rol FROM roles WHERE _estado <> 'X' AND rolID <> 1 ORDER BY rol");
$opciones_select = $db->obtenerTodo("SELECT o.opcionID, o.opcion, g.grupo 
                                    FROM opciones o 
                                    INNER JOIN grupos g ON o.grupoID = g.grupoID 
                                    WHERE o._estado <> 'X' 
                                    ORDER BY g.grupo, o.opcion");

// Listado de accesos actuales
$sql = "SELECT a.accesoID, r.rol, o.opcion, g.grupo
        FROM accesos a
        INNER JOIN roles r ON a.rolID = r.rolID
        INNER JOIN opciones o ON a.opcionID = o.opcionID
        INNER JOIN grupos g ON o.grupoID = g.grupoID
        WHERE a._estado <> 'X' AND r._estado <> 'X' AND o._estado <> 'X'
        AND r.rolID <> 1
        ORDER BY r.rol, g.grupo, o.opcion";
$accesos = $db->obtenerTodo($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Configuración de Accesos - Sistema</title>
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
                    <h3 class="mb-0">MATRIZ DE PERMISOS (ACCESOS)</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-lock"></i> CONCEDER NUEVO ACCESO
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>ROL</th>
                                    <th>GRUPO</th>
                                    <th>OPCIÓN</th>
                                    <th width="15%" class="text-center">REVOCAR</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accesos as $a): ?>
                                    <tr>
                                        <td><span class=""><?= htmlspecialchars($a['rol']) ?></span></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($a['grupo']) ?></small></td>
                                        <td class="fw-bold"><?= htmlspecialchars($a['opcion']) ?></td>
                                        <td class="text-center">
                                            <?php if ($a['rol'] !== 'ADMINISTRADOR'): ?>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="revocarAcceso(<?= $a['accesoID'] ?>, '<?= addslashes($a['rol']) ?>', '<?= addslashes($a['opcion']) ?>')">
                                                    <i class="fas fa-user-shield"></i> REVOCAR
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-dark">BLOQUEADO</span>
                                            <?php endif; ?>
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

    <!-- Modal Nuevo Acceso -->
    <div class="modal fade" id="modalAcceso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header" style="border-bottom: none;">
                        <h5 class="modal-title">Conceder Permiso de Visualización</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="border:none; background:none; font-size:1.5rem;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="conceder">

                        <div class="mb-3">
                            <label class="form-label fw-bold">1. Seleccione un Rol:</label>
                            <select name="rolID" class="form-control" required>
                                <?php foreach ($roles_select as $rs): ?>
                                    <option value="<?= $rs['rolID'] ?>"><?= htmlspecialchars($rs['rol']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">2. Seleccione la Pestaña a mostrar:</label>
                            <select name="opcionID" class="form-control" required>
                                <?php foreach ($opciones_select as $os): ?>
                                    <option value="<?= $os['opcionID'] ?>">[<?= htmlspecialchars($os['grupo']) ?>] -
                                        <?= htmlspecialchars($os['opcion']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle"></i> Esto hará que la pestaña sea visible automáticamente en
                            el panel del usuario al iniciar sesión.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Activar Acceso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Revocación -->
    <div class="modal fade" id="modalConfirmarRevocar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-user-shield me-2"></i>CONFIRMAR REVOCACIÓN</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">¿Realmente desea revocar el acceso del Rol:<br><b id="revRolNombre" class="text-danger fs-5"></b></p>
                    <p class="mt-2">Para la pestaña: <b id="revOpcionNombre" class="text-primary"></b>?</p>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <button type="button" id="btnConfirmarRevocar" class="btn btn-danger fw-bold">SÍ, REVOCAR</button>
                </div>
            </div>
        </div>
    </div>

    <form id="formRevocar" method="post" style="display:none;">
        <input type="hidden" name="accion" value="revocar">
        <input type="hidden" name="accesoID" id="revAccesoID">
    </form>

    <script>
        const modal = new bootstrap.Modal(document.getElementById('modalAcceso'));

        function abrirModal() {
            modal.show();
        }

        function revocarAcceso(id, rol, opcion) {
            document.getElementById('revAccesoID').value = id;
            document.getElementById('revRolNombre').innerText = rol;
            document.getElementById('revOpcionNombre').innerText = opcion;
            const modalRevocar = new bootstrap.Modal(document.getElementById('modalConfirmarRevocar'));
            modalRevocar.show();
        }

        document.getElementById('btnConfirmarRevocar').addEventListener('click', function() {
            document.getElementById('formRevocar').submit();
        });
    </script>
</body>

</html>