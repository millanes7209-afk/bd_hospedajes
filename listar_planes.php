<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SELECT * FROM planes WHERE tipo = 'CORTO PLAZO' AND estado <> 'X' ORDER BY planID DESC");
if ($res) {
    foreach($res as $p) {
        echo "- [" . $p['planID'] . "] " . $p['titulo'] . ": " . ($p['tarea'] ?? $p['descripcion'] ?? 'Sin detalle') . " (" . $p['estado'] . ")\n";
    }
} else {
    echo "No hay planes a corto plazo registrados.";
}
?>
