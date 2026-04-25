<?php
require_once(__DIR__ . '/../conexion.php');
try {
    $plan = "Permisos dinámicos: El select de permisos debe ocultar las opciones que el rol ya tiene asignadas.";
    $db->ejecutar("INSERT INTO planes (tarea, tipo, estado) VALUES (?, 'CORTO PLAZO', 'PENDIENTE')", [$plan]);
    echo "Plan registrado correctamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
