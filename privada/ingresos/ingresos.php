<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Verificar si la sesión del usuario está iniciada
if (empty($_SESSION['sesion_id_usuario'])) {
    die("Error: No se ha iniciado sesión.");
}

// Obtener el nombre de usuario de la sesión
$usuario = $_SESSION['sesion_usuario']; // Asegúrate de que esta variable esté definida en la sesión

// Consulta SQL para obtener los ingresos del usuario actual
$sql_ingresos = $db->Prepare("SELECT i.ingresoID, i.monto, i.tipo,
                                     i.descripcion, i.fecha_pago, fp.tipo as formapago,
                                     u.usuario as nombre_usuario
                              FROM ingresos i
                              JOIN formas_pago fp ON i.formaPagoID = fp.formaPagoID
                              JOIN usuarios u ON i._usuario = u.id_usuario
                              WHERE u.usuario = ?
                              AND i._estado <> 'X'
                              ORDER BY i.ingresoID DESC");
$rs_ingresos = $db->GetAll($sql_ingresos, array($usuario)); // Ejecutar la consulta


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresos del Usuario</title>
    <style>
        .table td, .table th {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Ingresos Generados por el Usuario</h1>

    <?php if (!empty($rs_ingresos) && is_array($rs_ingresos)) : ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Usuario</th>
                    <th>Monto</th>
                    <th>Tipo</th>
                    <th>Forma Pago</th>
                    <th>Descripción</th>
                    <th>Fecha de Pago</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rs_ingresos as $ingreso) : ?>
                    <tr>
                        <td><?= htmlspecialchars($ingreso['nombre_usuario']) ?></td>
                        <td><?= htmlspecialchars(number_format($ingreso['monto'], 2)) ?></td>
                        <td><?= htmlspecialchars($ingreso['tipo']) ?></td>
                        <td><?= htmlspecialchars($ingreso['formapago']) ?></td>
                        <td><?= htmlspecialchars($ingreso['descripcion']) ?></td>
                        <td><?= htmlspecialchars($ingreso['fecha_pago']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                
            </tfoot>
        </table>
    <?php else : ?>
        <div class="alert alert-warning">No hay ingresos generados por este usuario.</div>
    <?php endif; ?>
</div>

<script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>