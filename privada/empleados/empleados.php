<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// ✅ OBTENER empresaID desde sesión
$empresaID = $_SESSION['empresaID'];

// Obtener listado de roles para el modal de contrato (EXCLUYENDO ADMINISTRADOR)
$roles_select = $db->obtenerTodo("SELECT rolID, rol FROM roles WHERE _estado <> 'X' AND rol <> 'ADMINISTRADOR' ORDER BY rol");

// Consulta con información de usuario
$sql = "SELECT e.*, ee.sueldo, ee.fecha_inicio, ee.rolID, ee.estado_laboral,
                             r.rol AS cargo,
                             CONCAT_WS(' ', e.apellidos, e.nombres) AS empleado,
                             u.usuario, u.usuarioID
                             FROM empleados e
                             INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
                             INNER JOIN roles r ON ee.rolID = r.rolID
                             LEFT JOIN usuarios u ON e.empleadoID = u.empleadoID AND u._estado <> 'X'
                             WHERE e._estado <> 'X'
                             AND e.empleadoID > 1
                             AND ee.empresaID = ?
                             AND ee.estado_laboral = 'ACTIVO'
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

        .modal {
            display: none;
            position: fixed;
            z-index: 1050;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
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
        .btn-close,
        .modal-header button {
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

        .btn-close:hover,
        .modal-header button:hover {
            opacity: 1;
            background-color: #f8f9fa;
            color: #000;
        }

        .btn-close:focus,
        .modal-header button:focus {
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

        .modal-body {
            padding: 1rem;
        }

        .btn {
            padding: 5px 10px;
            cursor: pointer;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .modal-backdrop {
            position: fixed;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
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
                <a href="empleado_nuevo.php?auth=empleados.php" class="btn btn-success mb-3" role="button">🔍 Agregar Empleado</a>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>C.I.</th>
                            <th>Empleado</th>
                            <th>Teléfono</th>
                            <th>Cargo</th>
                            <th>Fecha Inicio</th>
                            <th>Sueldo</th>
                            <th>Usuario</th>
                            <th>Modificar</th>
                            <th>Baja Laboral</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rs): ?>
                            <?php $b = 1;
                            foreach ($rs as $fila): ?>
                                <tr>
                                    <td><?php echo $b++; ?></td>
                                    <td><?php echo $fila['ci']; ?></td>
                                    <td><?php echo $fila['empleado']; ?></td>
                                    <td><?php echo !empty($fila['telefono']) ? $fila['telefono'] : '<span class="text-muted small">-</span>'; ?></td>
                                    <td><span><?php echo htmlspecialchars($fila['cargo']); ?></span></td>
                                    <td><?php echo date("d/m/Y", strtotime($fila['fecha_inicio'])); ?></td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Bs. <?php echo number_format($fila['sueldo'], 2, ',', '.'); ?></span>
                                            <button class="btn btn-link btn-sm p-0 ms-2 btn-edit-contrato"
                                                title="Modificar Contrato" data-id="<?php echo $fila['empleadoID']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($fila['empleado']); ?>"
                                                data-sueldo="<?php echo $fila['sueldo']; ?>"
                                                data-rolid="<?php echo $fila['rolID']; ?>">
                                                <i class="fas fa-briefcase text-primary"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($fila['usuario'])): ?>
                                            <span><?php echo htmlspecialchars($fila['usuario']); ?></span>

                                            <?php
                                            // LÓGICA DE JERARQUÍA PARA LA LLAVE
                                            $adminRol = strtoupper($_SESSION['sesion_rol'] ?? '');
                                            $targetRol = strtoupper($fila['cargo'] ?? '');
                                            $mostrarLlave = false;

                                            if ($adminRol === 'ADMINISTRADOR') {
                                                $mostrarLlave = true; // El Dios ve todo
                                            } elseif ($adminRol === 'PROPIETARIO' && $targetRol !== 'ADMINISTRADOR' && $targetRol !== 'PROPIETARIO') {
                                                $mostrarLlave = true; // El Propietario solo ve a rangos inferiores
                                            }

                                            if ($mostrarLlave): ?>
                                                <button class="btn btn-link btn-sm p-0 ms-2 btn-reset-pass" title="Resetear Contraseña"
                                                    data-id="<?php echo $fila['empleadoID']; ?>"
                                                    data-user="<?php echo htmlspecialchars($fila['usuario']); ?>">
                                                    <i class="fas fa-key text-success"></i>
                                                </button>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <span class="text-muted small">Sin usuario</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <form method="post" action="Empleado_modificar.php">
                                            <input type="hidden" name="empleadoID" value="<?php echo $fila['empleadoID']; ?>">
                                            <input type="hidden" name="auth" value="empleados.php">
                                            <button type="submit" style="background:none; border:none; color:#0d6efd; padding:0; cursor:pointer;" title="Modificar Ficha">
                                                <i class="fas fa-pencil-alt fa-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-center">
                                        <button class="dar-baja" style="background:none; border:none; color:#dc3545; padding:0; cursor:pointer;"
                                            data-empleadoid="<?php echo $fila['empleadoID']; ?>"
                                            data-nombre="<?php echo $fila['empleado']; ?>" title="Dar de Baja Laboral">
                                            <i class="fas fa-user-minus fa-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">No hay personal activo.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include("modales_empleados.php"); ?>

        <?php include("js_empleados.php"); ?>

</body>

</html>