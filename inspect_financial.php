<?php
require_once('conexion.php');
function desc($table) {
    global $db;
    echo "\n=== $table ===\n";
    try {
        $res = $db->obtenerTodo("DESCRIBE $table");
        foreach($res as $r) echo "  {$r['Field']} ({$r['Type']})\n";
    } catch(Exception $e) { echo "  [Error or Table missing]\n"; }
}
desc('gastos');
desc('servicios_extra');
?>
