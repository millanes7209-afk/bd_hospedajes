<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW INDEX FROM hospedajes");
echo "INDICES DE HOSPEDAJES:\n";
foreach ($res as $r) {
    echo "Clave: " . $r['Key_name'] . " -> Columna: " . $r['Column_name'] . "\n";
}

echo "\nINDICES DE HABITACIONES:\n";
$res2 = $db->obtenerTodo("SHOW INDEX FROM habitaciones");
foreach ($res2 as $r) {
    echo "Clave: " . $r['Key_name'] . " -> Columna: " . $r['Column_name'] . "\n";
}
?>