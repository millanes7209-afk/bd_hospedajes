<?php
require 'conexion.php';
$planes = $db->obtenerTodo('SELECT * FROM planes ORDER BY id DESC LIMIT 20');
echo "<pre>";
print_r($planes);
echo "</pre>";
?>
