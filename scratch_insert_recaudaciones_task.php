<?php
require_once("conexion.php");
try {
    $db->ejecutar("INSERT INTO planes (tarea, tipo, estado) VALUES ('Tabla de recaudaciones (Solo Propietarios) - Entrega de dinero', 'CORTO', 'PENDIENTE')");
    echo "SUCCESS: Tarea registrada.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
