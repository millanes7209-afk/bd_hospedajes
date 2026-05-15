<?php
require 'conexion.php';
try {
    $db->ejecutar("ALTER TABLE banos ADD COLUMN recaudacionID INT(11) DEFAULT NULL");
    echo "COLUMNA 'recaudacionID' AÑADIDA A 'banos'.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
