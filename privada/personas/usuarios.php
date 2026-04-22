<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];

// Consulta para obtener los datos de los usuarios filtrados por empresa actual
$sql = "   SELECT      u.*, CONCAT_WS(' ', e.apellidos, e.nombres) as empleado
                        FROM        usuarios u
                        INNER JOIN  empleados e ON u.empleadoID = e.empleadoID
                        INNER JOIN  empleado_empresas ee ON e.empleadoID = ee.empleadoID
                        WHERE       u._estado <> 'X'
                        AND         e._estado <> 'X'
                        AND         ee._estado <> 'X'
                        AND         ee.empresaID = ?
                        AND         u.usuarioID > 1
                        ORDER BY u.usuarioID DESC
";

$rs = $db->obtenerTodo($sql, array($empresaID));

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Listado de Usuarios</title>
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

        /* Estilos del modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            outline: 0;
        }

        .modal-dialog {
            position: relative;
            margin: 10px;
            max-width: 500px;
            margin: auto;
            top: 20%;
        }

        .modal-content {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.3rem;
        }

        .modal-header,
        .modal-footer {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
        }

        .modal-body {
            padding: 1rem;
        }

        .modal-header .close {
            border: none;
            background: transparent;
        }

        .btn {
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            display: inline-block;
            text-align: center;
            cursor: pointer;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h3>GESTIÓN USUARIOS</h3>
        </div>
        <div class="card-body">
            <div id="mensaje"></div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="usuario_nuevo.php" class="btn btn-success mb-3" role="button">Agregar Usuario</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">N°</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Empleado</th>
                            <th scope="col"><img src='../../imagenes/modificar.gif'></th>
                            <th scope="col"><img src='../../imagenes/borrar.jpeg'></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs): ?>
                            <?php $b = 1; ?>
                            <?php foreach ($rs as $fila): ?>
                                <tr>
                                    <td><?php echo $b; ?></td>
                                    <td><?php echo $fila['usuario']; ?></td>
                                    <td><?php echo $fila['empleado']; ?></td>
                                    <td>
                                        <form name="formModif<?php echo $fila['usuarioID']; ?>" method="post"
                                            action="usuario_modificar.php" style="display:inline;">
                                            <input type="hidden" name="usuarioID" value="<?php echo $fila['usuarioID']; ?>">
                                            <input type="hidden" name="empleadoID" value="<?php echo $fila['empleadoID']; ?>">
                                            <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger eliminar-usuario"
                                            data-usuarioid="<?php echo $fila['usuarioID']; ?>"
                                            data-usuario="<?php echo $fila['usuario']; ?>">Eliminar</button>
                                    </td>
                                </tr>
                                <?php $b++; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div class="modal" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <!-- Contenido del modal -->
                <div class="modal-header">
                    <h5 class="modal-title">Confirmación de Eliminación</h5>
                    <button type="button" class="close" id="closeModalBtn" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    ¿Está seguro de que desea eliminar al usuario <span id="usuarioNombre"></span>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelModalBtn">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showModal() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'block';

            if (!document.querySelector('.modal-backdrop')) {
                document.body.insertAdjacentHTML('beforeend', '<div class="modal-backdrop"></div>');
            }
        }

        function hideModal() {
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'none';

            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }

        document.querySelectorAll('.eliminar-usuario').forEach(boton => {
            boton.addEventListener('click', function (event) {
                event.preventDefault();

                const usuarioID = this.getAttribute('data-usuarioid');
                const usuario = this.getAttribute('data-usuario');

                document.getElementById('usuarioNombre').textContent = usuario;

                showModal();

                document.getElementById('confirmDeleteBtn').onclick = function () {
                    fetch('usuario_eliminar.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id_usuario: usuarioID })
                    })
                        .then(response => response.json())
                        .then(data => {
                            const mensajeDiv = document.getElementById('mensaje');
                            mensajeDiv.innerHTML = `<div class="alert alert-${data.tipo}">${data.mensaje}</div>`;

                            if (data.tipo === 'success') {
                                setTimeout(() => { window.location.reload(); }, 2000);
                            }

                            hideModal();
                        })
                        .catch(error => console.error('Error:', error));
                };
            });
        });

        document.getElementById('closeModalBtn').addEventListener('click', hideModal);
        document.getElementById('cancelModalBtn').addEventListener('click', hideModal);
    </script>
</body>

</html>