<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 31");
echo "Tarea 31 marcada como TERMINADO.\n";
