<?php
require_once("conexion.php");

try {
    $db->ejecutar("ALTER TABLE hospedajes ADD COLUMN cajaID INT NULL");
    echo "Column cajaID added successfully.\n";
} catch (Exception $e) {
    echo "Notice: cajaID might already exist or error: " . $e->getMessage() . "\n";
}

try {
    $db->ejecutar("UPDATE hospedajes SET cajaID = 17");
    echo "All hospedajes updated to cajaID=17 successfully.\n";
} catch (Exception $e) {
    echo "Error updating hospedajes: " . $e->getMessage() . "\n";
}
?>
