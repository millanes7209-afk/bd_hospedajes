<?php
require_once('conexion.php');
echo "=== TABLAS RELACIONADAS CON HABITACIONES ===\n";
$tables = $db->obtenerTodo("SHOW TABLES LIKE '%habitacion%'");
foreach($tables as $t) {
    $tableName = current($t);
    echo "\nTABLE: $tableName\n";
    $cols = $db->obtenerTodo("DESCRIBE $tableName");
    foreach($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
}
?>
