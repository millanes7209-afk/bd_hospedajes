<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
echo "--- USUARIOS ---\n";
$res = $db->obtenerTodo("DESCRIBE usuarios");
foreach ($res as $row)
    echo $row['Field'] . "\n";

echo "\n--- ROLES ---\n";
$res = $db->obtenerTodo("SELECT * FROM roles");
foreach ($res as $row)
    echo $row['rolID'] . ": " . $row['rol'] . "\n";
