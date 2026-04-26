<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 30");
echo "Tarea 30 marcada como TERMINADO.\n";
