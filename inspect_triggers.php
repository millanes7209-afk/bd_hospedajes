<?php
require 'conexion.php';
try {
    $triggers = $db->obtenerTodo("SHOW TRIGGERS");
    echo "--- TRIGGERS ENCONTRADOS ---\n";
    foreach ($triggers as $t) {
        echo "Tabla: " . $t['Table'] . " | Evento: " . $t['Event'] . " | Timing: " . $t['Timing'] . "\n";
        echo "Sentencia: " . $t['Statement'] . "\n";
        echo "------------------------------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>