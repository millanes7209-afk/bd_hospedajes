<?php
require_once("conexion.php");
try {
    $db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE tarea = 'Seguridad AJAX'");
    echo "SUCCESS: Estado actualizado en la DB.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
