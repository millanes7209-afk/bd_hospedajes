<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SELECT * FROM planes WHERE tipo = 'CORTO' AND estado <> 'X' ORDER BY planID DESC");
if ($res) {
    foreach($res as $p) {
        echo "- [" . $p['planID'] . "] " . $p['titulo'] . ": " . ($p['tarea'] ?? 'Sin detalle') . " (" . $p['estado'] . ")\n";
    }
} else {
    echo "No se encontraron planes con tipo 'CORTO'.";
}
?>
