<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 25");
echo "Tarea 25 marcada como TERMINADO.\n";
