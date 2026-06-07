<?php
session_start();
require_once("../../conexion.php");

// 1. PROCESAR ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuarioID = $_SESSION['sesion_id_usuario'] ?? 1;
    $empresaID = $_SESSION['empresaID'];

    if ($accion === 'guardar') {
        $tipo = strtoupper(trim($_POST['tipo']));
        $descripcion = trim($_POST['descripcion']);
        $formapagoID = $_POST['formapagoID'] ?? null;
        $ahora = date('Y-m-d H:i:s');

        if ($formapagoID) {
            // Actualizar
            $sql = "UPDATE formas_pago SET tipo = ?, descripcion = ?, _fec_modificacion = ?, _usuario = ? 
                    WHERE formapagoID = ? AND empresaID = ?";
            $db->ejecutar($sql, [$tipo, $descripcion, $ahora, $usuarioID, $formapagoID, $empresaID]);
        } else {
            // Insertar
            $sql = "INSERT INTO formas_pago (empresaID, tipo, descripcion, _fec_insercion, _usuario, _estado) 
                    VALUES (?, ?, ?, ?, ?, 'A')";
            $db->ejecutar($sql, [$empresaID, $tipo, $descripcion, $ahora, $usuarioID]);
        }
    } elseif ($accion === 'eliminar') {
        $formapagoID = $_POST['formapagoID'];
        $sql = "UPDATE formas_pago SET _estado = 'X', _fec_modificacion = ?, _usuario = ? 
                WHERE formapagoID = ? AND empresaID = ?";
        $db->ejecutar($sql, [$ahora, $usuarioID, $formapagoID, $empresaID]);
    }
    header("Location: formas_pago.php");
    exit();
}

require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];
$sql = "SELECT * FROM formas_pago WHERE empresaID = ? AND _estado <> 'X' ORDER BY tipo ASC";
$rs = $db->obtenerTodo($sql, [$empresaID]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Formas de Pago | Premium</title>
    <style>
        thead { color: black; background: #b5b5b5; }
        .card { margin: 20px; }
        .table-sm td, .table-sm th { padding: 0.3rem; }
    </style>
</head>

<body>
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">FORMAS DE PAGO</h3>
                    <button class="btn btn-success btn-sm fw-bold" onclick="abrirModal()">
                        <i class="fas fa-plus"></i> NUEVA FORMA DE PAGO
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>TIPO / NOMBRE</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th width="15%" class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rs)): ?>
                                    <?php foreach ($rs as $fila): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($fila['tipo']) ?></td>
                                            <td><?= htmlspecialchars($fila['descripcion']) ?></td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-4">
                                                    <button style="background:none; border:none; color:#17a2b8; padding:0; cursor:pointer;" 
                                                        title="Modificar" onclick="editarForma(<?= $fila['formapagoID'] ?>, '<?= addslashes($fila['tipo']) ?>', '<?= addslashes($fila['descripcion']) ?>')">
                                                        <i class="fas fa-pencil-alt fa-lg"></i>
                                                    </button>
                                                    <button style="background:none; border:none; color:#343a40; padding:0; cursor:pointer;" 
                                                        title="Eliminar" onclick="eliminarForma(<?= $fila['formapagoID'] ?>, '<?= htmlspecialchars($fila['tipo']) ?>')">
                                                        <i class="fas fa-trash-alt fa-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">No hay registros.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Formulario -->
    <div class="modal fade" id="modalForma" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nueva Forma de Pago</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="outline:none; border:none; background:none; font-size:1.5rem;">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" value="guardar">
                        <input type="hidden" name="formapagoID" id="formapagoID">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nombre / Tipo:</label>
                            <input type="text" name="tipo" id="txtTipo" class="form-control" required onkeyup="this.value=this.value.toUpperCase()">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción Corta:</label>
                            <input type="text" name="descripcion" id="txtDescripcion" class="form-control" required>
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

    <!-- Modal Eliminar (Simple Negro) -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-dark">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="fas fa-trash-alt"></i> CONFIRMAR ELIMINACIÓN</h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close" style="outline:none; border:none; background:none; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">¿Realmente desea eliminar la forma de pago: <br><b id="delNombre" class="text-dark fs-5"></b>?</p>
                </div>
                <div class="modal-footer bg-light justify-content-center">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">CANCELAR</button>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="accion" value="eliminar">
                        <input type="hidden" name="formapagoID" id="delID">
                        <button type="submit" class="btn btn-dark fw-bold">SÍ, ELIMINAR</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modalF = new bootstrap.Modal(document.getElementById('modalForma'));
        const modalE = new bootstrap.Modal(document.getElementById('modalEliminar'));

        function abrirModal() {
            document.getElementById('modalTitle').innerText = 'Nueva Forma de Pago';
            document.getElementById('formapagoID').value = '';
            document.getElementById('txtTipo').value = '';
            document.getElementById('txtDescripcion').value = '';
            modalF.show();
        }

        function editarForma(id, tipo, desc) {
            document.getElementById('modalTitle').innerText = 'Editar Forma de Pago';
            document.getElementById('formapagoID').value = id;
            document.getElementById('txtTipo').value = tipo;
            document.getElementById('txtDescripcion').value = desc;
            modalF.show();
        }

        function eliminarForma(id, nombre) {
            document.getElementById('delID').value = id;
            document.getElementById('delNombre').innerText = nombre;
            modalE.show();
        }
    </script>
</body>

</html>