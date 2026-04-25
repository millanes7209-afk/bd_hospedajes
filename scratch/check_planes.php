<?php
require_once(__DIR__ . '/../conexion.php');
$res = $db->obtenerTodo("DESCRIBE planes");
foreach ($res as $row) {
    echo "Field: {$row['Field']}\n";
}
?>
