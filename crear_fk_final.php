<?php
require_once("conexion.php");

$sql = "ALTER TABLE hospedajes_auditoria_montos 
        ADD CONSTRAINT fk_auditoria_hospedaje 
        FOREIGN KEY (hospedajeID) REFERENCES hospedajes(hospedajeID) 
        ON DELETE RESTRICT";

if ($db->ejecutar($sql) !== false) {
    echo "FOREIGN KEY creada con éxito. Restricción ON DELETE RESTRICT activada.";
} else {
    echo "Error al crear la FOREIGN KEY. Es posible que existan registros huérfanos.";
}
?>
