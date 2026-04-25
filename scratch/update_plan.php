<?php
require_once(__DIR__ . '/../conexion.php');
$db->ejecutar("UPDATE planes SET estado = 'FINALIZADO' WHERE planID = 19 OR tarea LIKE '%Los movimientos no detallan%'");
echo "Plan marcado como finalizado en la BD.";
?>
