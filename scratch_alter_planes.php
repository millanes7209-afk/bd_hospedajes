<?php
require_once("conexion.php");
try {
    $db->ejecutar("ALTER TABLE planes ADD COLUMN tipo ENUM('CORTO', 'LARGO') NOT NULL AFTER tarea");
    echo "SUCCESS: Columna 'tipo' añadida correctamente.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
