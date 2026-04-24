<?php
require_once("conexion.php");
try {
    $db->ejecutar("INSERT INTO planes (tarea, plazo, estado) VALUES ('Seguridad AJAX', 'CORTO', 'PENDIENTE')");
    echo "SUCCESS: Tarea anotada en la DB.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
