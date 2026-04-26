<?php
require_once("conexion.php");
$sql = "SELECT planID, tarea FROM planes WHERE estado = 'PENDIENTE' ORDER BY planID ASC";
$planes = $db->obtenerTodo($sql);
echo "=== PLAN DE TRABAJO COMPLETO (PENDIENTE) ===\n\n";
foreach ($planes as $p) {
    echo "[ID: {$p['planID']}] - {$p['tarea']}\n";
}
