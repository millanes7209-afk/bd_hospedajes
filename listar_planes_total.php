<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SELECT * FROM planes");
if ($res) {
    foreach($res as $p) {
        echo "[" . ($p['tipo'] ?? 'S/T') . "] " . ($p['titulo'] ?? 'S/T') . " -> " . ($p['tarea'] ?? 'S/D') . " (" . ($p['estado'] ?? 'S/E') . ")\n";
    }
} else {
    echo "La tabla 'planes' está totalmente vacía.";
}
?>
