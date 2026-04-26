<?php
require_once("conexion.php");
$planes = $db->obtenerTodo("SELECT * FROM planes ORDER BY planID ASC");
echo "=== HISTORIAL COMPLETO DE PLANES ===\n\n";
foreach ($planes as $p) {
    echo "[ID: {$p['planID']}] - {$p['tarea']} [{$p['estado']}]\n";
}
