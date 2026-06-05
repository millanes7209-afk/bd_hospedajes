<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
echo "--- EMPLEADO_EMPRESAS ---\n";
$res = $db->obtenerTodo("DESCRIBE empleado_empresas");
foreach ($res as $row)
    echo $row['Field'] . "\n";

echo "\n--- EMPLEADOS ---\n";
$res = $db->obtenerTodo("DESCRIBE empleados");
foreach ($res as $row)
    echo $row['Field'] . "\n";
