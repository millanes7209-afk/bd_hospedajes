<?php
require_once("conexion.php");
$stmt = $db->ejecutar("DESCRIBE planes");
$columnas = $stmt->fetchAll();
foreach ($columnas as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ")\n";
}
