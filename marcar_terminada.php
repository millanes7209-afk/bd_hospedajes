<?php
require_once("conexion.php");
$sql = "UPDATE planes SET estado = 'TERMINADO' WHERE tarea LIKE '%FK en hospedajes_auditoria_montos%'";
if ($db->ejecutar($sql) !== false) {
    echo "Tarea marcada como TERMINADA en la tabla planes.";
}
?>
