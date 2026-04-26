<?php
require_once("conexion.php");

try {
    $sql = "SELECT * FROM planes ORDER BY planID DESC";
    $planes = $db->obtenerTodo($sql);
    
    echo "=== LISTA DE PLANES (BASE DE DATOS) ===\n\n";
    foreach ($planes as $plan) {
        echo "[ID: {$plan['planID']}] - {$plan['tarea']}\n";
        echo "Tipo: {$plan['tipo']} | Estado: {$plan['estado']}\n";
        echo "-------------------------------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
