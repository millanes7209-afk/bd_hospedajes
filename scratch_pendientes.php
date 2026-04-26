<?php
require_once("conexion.php");
$sql = "SELECT planID, tarea FROM planes WHERE estado = 'PENDIENTE' AND tarea NOT LIKE '%MOMENTANEO%' ORDER BY planID ASC";
$planes = $db->obtenerTodo($sql);
echo "=== TAREAS PENDIENTES (Sin Momentáneos) ===\n\n";
foreach ($planes as $p) {
    echo "[ID: {$p['planID']}] - {$p['tarea']}\n";
}
