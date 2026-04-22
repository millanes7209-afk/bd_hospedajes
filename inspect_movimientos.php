<?php
require_once('conexion.php');
echo "=== ESTRUCTURA MOVIMIENTOS ===\n";
$cols = $db->obtenerTodo("DESCRIBE movimientos");
foreach($cols as $c) echo "  {$c['Field']} ({$c['Type']})\n";
?>
