<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// ✅ OBTENER empresaID desde sesión
$empresaID = $_SESSION['empresaID'];

// Consulta con información de usuario
                $sql =      "SELECT e.*, ee.sueldo, ee.fecha_inicio, CONCAT_WS(' ', e.apellidos, e.nombres) AS empleado,
                            u.usuario, u.usuarioID
                            FROM empleados e
                            INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
                            LEFT JOIN usuarios u ON e.empleadoID = u.empleadoID AND u._estado <> 'X'
                            WHERE e._estado <> 'X'
                            AND e.empleadoID > 1
                            AND ee.empresaID = ?
                            ORDER BY e.empleadoID ASC
                        ";

$rs = $db->obtenerTodo($sql, array($empresaID));
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Empleados</title>
    <style>
        thead { color: black; background: #b5b5b5; }
        .card { margin: 20px; }
        tr { color: black; }

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0; top: 0;
            width: 100%; height: 100%;
        }

        .modal-dialog {
            margin: auto;
            max-width: 500px;
            top: 20%;
            position: relative;
        }

        .modal-content {
            background-color: #fff;
            border: 1px solid #dee2e6;
        }

        .modal-header {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            border-bottom: none;
        }
        
        /* Estilo Empleadolizado para todas las X de cerrar modales */
        .btn-close, .modal-header button {
            background: none;
            border: none;
            font-size: 1.5rem;
            font-weight: bold;
            color: #000;
            opacity: 0.5;
            cursor: pointer;
            padding: 0;
            margin: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s ease;
        }
        
        .btn-close:hover, .modal-header button:hover {
            opacity: 1;
            background-color: #f8f9fa;
            color: #000;
        }
        
        .btn-close:focus, .modal-header button:focus {
            opacity: 1;
            box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.25);
            outline: none;
        }
        
        .modal-footer {
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            border-top: none;
        }

        .modal-body { padding: 1rem; }

        .btn { padding: 5px 10px; cursor: pointer; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }

        .modal-backdrop {
            position: fixed;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
        }
    </style>
</head>

<body>
    <div class="card">
    <div class="card-header">
        <h3>GESTIÓN EMPLEADOS</h3>
    </div>

    <div class="card-body">
        <div id="mensaje"></div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <a href="empleado_nuevo.php" class="btn btn-success mb-3" role="button">🔍 Agregar Empleado</a>
        </div>
        <div class="table-responsive">
             <table class="table table-striped">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>C.I.</th>
                        <th>Empleado</th>
                        <th>Teléfono</th>
                        <th>Fecha Contratación</th>
                        <th>Sueldo</th>
                        <th>Usuario</th>
                        <th>Modificar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($rs) : ?>
                    <?php $b = 1; ?>
                    <?php foreach ($rs as $fila) : ?>
                        <tr>
                            <td><?php echo $b; ?></td>
                            <td><?php echo $fila['ci']; ?></td>
                            <td><?php echo $fila['empleado']; ?></td>
                            <td><?php echo $fila['telefono']; ?></td>
                            <td><?php echo $fila['fecha_inicio']; ?></td>
                            <td><?php echo number_format($fila['sueldo'], 2, ',', '.'); ?></td>
                            <td>
                                <?php if (!empty($fila['usuario'])): ?>
                                    <span class="badge bg-success text-white"><?php echo htmlspecialchars($fila['usuario']); ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Sin usuario</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" action="Empleado_modificar.php">
                                    <input type="hidden" name="empleadoID" value="<?php echo $fila['empleadoID']; ?>">
                                    <button type="submit" class="btn btn-sm btn-primary btn-accion">Modificar</button>
                                </form>
                            </td>
                            <td>
                                        <button class="btn btn-sm btn-danger eliminar-usuario" data-usuarioid="<?php echo $fila['empleadoID']; ?>" data-usuario="<?php echo $fila['usuario']; ?>">Eliminar</button>
                                    </td>
                        </tr>

                    <?php $b++; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

                </tbody>
            </table>
    </div>
</div>

<!-- Modal -->
<div class="modal" id="confirmModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Confirmación</h5>
                <button id="closeModalBtn">X</button>
            </div>
            <div class="modal-body">
                ¿Eliminar a <span id="EmpleadoNombre"></span>?
            </div>
            <div class="modal-footer">
                <button id="cancelModalBtn">Cancelar</button>
                <button id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
function showModal() {
    document.getElementById('confirmModal').style.display = 'block';
    document.body.insertAdjacentHTML('beforeend','<div class="modal-backdrop"></div>');
}

function hideModal() {
    document.getElementById('confirmModal').style.display = 'none';
    let b = document.querySelector('.modal-backdrop');
    if (b) b.remove();
}

document.querySelectorAll('.eliminar-Empleado').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        let nombre = this.dataset.nombre;

        document.getElementById('EmpleadoNombre').textContent = nombre;
        showModal();

        document.getElementById('confirmDeleteBtn').onclick = function() {
            fetch('Empleado_eliminar.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: 'id_Empleado=' + id
            })
            .then(r => r.json())
            .then(data => {
                alert(data.mensaje);
                location.reload();
            });
        };
    });
});

document.getElementById('closeModalBtn').onclick = hideModal;
document.getElementById('cancelModalBtn').onclick = hideModal;
</script>

</body>
</html>
