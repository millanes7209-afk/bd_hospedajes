<?php
require_once('conexion.php');
echo "--- ROLES ---\n";
$roles = $db->obtenerTodo("SELECT * FROM roles");
foreach($roles as $r) echo "ID: {$r['rolID']} | Rol: {$r['rol']}\n";

echo "\n--- ESTRUCTURA OPCIONES ---\n";
$cols = $db->obtenerTodo("SHOW COLUMNS FROM opciones");
foreach($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";

echo "\n--- ESTRUCTURA ACCESOS ---\n";
$cols = $db->obtenerTodo("SHOW COLUMNS FROM accesos");
foreach($cols as $c) echo "{$c['Field']} ({$c['Type']})\n";
?>
