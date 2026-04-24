<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Consulta de usuarios activos vinculados a empleados con contrato ACTIVO en esta empresa
$sql = "SELECT u.usuarioID, u.usuario, 
               CONCAT_WS(' ', e.apellidos, e.nombres) AS empleado,
               r.rol AS cargo
        FROM usuarios u
        INNER JOIN empleados e ON u.empleadoID = e.empleadoID
        INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
        INNER JOIN roles r ON ee.rolID = r.rolID
        WHERE ee.empresaID = ? 
          AND ee.estado_laboral = 'ACTIVO'
          AND u._estado <> 'X'
        ORDER BY u.usuario ASC";

$rs = $db->obtenerTodo($sql, array($empresaID));
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - Dulces Sueños</title>
    <style>
        thead {
            color: black;
            background: #b5b5b5;
        }

        .card {
            margin: 20px;
        }

        tr {
            color: black;
        }

        /* Estilos para el modal de reset */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 15px;
            text-align: right;
        }
    </style>
</head>

<body class="bg-light">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">GESTIÓN DE ACCESOS Y CONTRASEÑAS</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Desde aquí puede resetear las contraseñas de los empleados. La nueva clave por defecto
                será: <b>123456</b></p>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Usuario</th>
                            <th>Empleado</th>
                            <th>Cargo Actual</th>
                            <th class="text-center">Seguridad</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs): ?>
                            <?php $b = 1;
                            foreach ($rs as $fila): ?>
                                <tr>
                                    <td>
                                        <?php echo $b++; ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?php echo htmlspecialchars($fila['usuario']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($fila['empleado']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($fila['cargo']); ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning fw-bold btn-reset"
                                            data-id="<?php echo $fila['usuarioID']; ?>"
                                            data-user="<?php echo htmlspecialchars($fila['usuario']); ?>">
                                            RESETEAR CLAVE
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No hay usuarios registrados para esta empresa.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN -->
    <div id="modalReset" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="mb-0">Resetear Contraseña</h5>
                <button type="button" class="btn-close" onclick="cerrarModal()"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea resetear la contraseña de <b id="nomUsuario"></b>?
                <br><br>
                La nueva clave será: <span class="text-danger fw-bold">123456</span>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" onclick="cerrarModal()">CANCELAR</button>
                <button id="btnConfirmarReset" class="btn btn-danger btn-sm">SÍ, RESETEAR</button>
            </div>
        </div>
    </div>

    <script>
        let usuarioIDGlobal = null;

        document.querySelectorAll('.btn-reset').forEach(btn => {
            btn.addEventListener('click', function () {
                usuarioIDGlobal = this.dataset.id;
                document.getElementById('nomUsuario').textContent = this.dataset.user;
                document.getElementById('modalReset').style.display = 'block';
            });
        });

        function cerrarModal() {
            document.getElementById('modalReset').style.display = 'none';
        }

        document.getElementById('btnConfirmarReset').addEventListener('click', function () {
            if (usuarioIDGlobal) {
                fetch('ajax_reset_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'usuarioID=' + usuarioIDGlobal
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.status === 'SUCCESS') {
                            alert('Contraseña reseteada con éxito a: 123456');
                            cerrarModal();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        });

        window.onclick = function (event) {
            if (event.target == document.getElementById('modalReset')) cerrarModal();
        }

        // Cerrar con Escape
        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') cerrarModal();
        });
    </script>
</body>

</html>