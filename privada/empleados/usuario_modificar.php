<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

$empresaID = $_SESSION['empresaID'];
$usuarioID = $_POST["usuarioID"] ?? $_GET["usuarioID"] ?? null;

if (!$usuarioID) {
    die("ID de usuario no proporcionado.");
}

// 1. Obtener información del usuario
$sql = "SELECT * FROM usuarios WHERE usuarioID = ? AND _estado = 'A'";
$fila = $db->obtenerFila($sql, [$usuarioID]);

if (!$fila) {
    die("Usuario no encontrado.");
}

$empleadoID_actual = $fila['empleadoID'];

// 2. Obtener lista de empleados de la empresa actual
$sql_empleados = "SELECT e.empleadoID, CONCAT_WS(' ', e.apellidos, e.nombres) as empleado 
                  FROM empleados e
                  INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
                  WHERE e._estado='A' AND ee.empresaID = ? AND ee._estado <> 'X'
                  ORDER BY e.apellidos ASC";
$rs_empleados = $db->obtenerTodo($sql_empleados, [$empresaID]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modificar Usuario</title>
    <style>
        .form-control { border-color: black; }
        .card-body { padding: 25px; }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>MODIFICAR USUARIO</h3>
                </div>
                <div class="card-body">
                    <form action="usuario_modificar1.php" method="post">
                        <input type="hidden" name="usuarioID" value="<?= $usuarioID ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">(*) Empleado</label>
                            <select class="form-control" name="empleadoID" required>
                                <?php foreach ($rs_empleados as $e): ?>
                                    <option value="<?= $e['empleadoID'] ?>" <?= $e['empleadoID'] == $empleadoID_actual ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($e['empleado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">(*) Nombre de Usuario</label>
                            <input type="text" class="form-control" name="usuario" value="<?= htmlspecialchars($fila['usuario']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">(*) Clave (Dejar en blanco si no desea cambiarla)</label>
                            <input type="password" class="form-control" name="clave">
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary" type="submit">Guardar Cambios</button>
                            <button class="btn btn-secondary" type="button" onclick="history.back()">Atrás</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
