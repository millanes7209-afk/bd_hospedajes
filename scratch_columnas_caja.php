<?php
require_once("conexion.php");
echo "=== TABLA cajas ===\n";
$stmt = $db->ejecutar("DESCRIBE cajas");
foreach ($stmt->fetchAll() as $col) echo $col['Field'] . " (" . $col['Type'] . ")\n";

echo "\n=== TABLA cierre_cajas ===\n";
$stmt = $db->ejecutar("DESCRIBE cierre_cajas");
foreach ($stmt->fetchAll() as $col) echo $col['Field'] . " (" . $col['Type'] . ")\n";
