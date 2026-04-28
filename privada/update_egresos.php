<?php
require_once("../conexion.php");
try {
    $db->ejecutar("ALTER TABLE egresos ADD COLUMN entregado TINYINT(1) DEFAULT 0, ADD COLUMN fecha_entrega DATETIME DEFAULT NULL, ADD COLUMN recaudacionID INT DEFAULT NULL");
    echo "Tabla egresos ACTUALIZADA.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
