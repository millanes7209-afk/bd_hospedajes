<?php
require_once("conexion.php");
if ($db->ejecutar("RENAME TABLE hospedajes_auditoria_montos TO auditorias") !== false) {
    echo "Tabla renombrada a 'auditorias' con éxito.";
} else {
    echo "Error al renombrar la tabla.";
}
?>
