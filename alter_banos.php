<?php
require 'conexion.php';
try {
    $db->ejecutar("ALTER TABLE banos ADD COLUMN entregado TINYINT(1) DEFAULT 0");
    echo "COLUMNA 'entregado' AÑADIDA CORRECTAMENTE.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
