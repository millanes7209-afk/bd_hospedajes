<?php
require_once 'conexion.php';
$cols = $db->obtenerTodo("DESCRIBE migracion_log");
foreach ($cols as $c) {
    echo $c['Field'] . " - " . $c['Type'] . "\n";
}
echo "\n--- DATOS ACTUALES ---\n";
$datos = $db->obtenerTodo("SELECT * FROM migracion_log");
print_r($datos);
