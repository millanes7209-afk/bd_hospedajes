<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 32");
echo "Tarea 32 marcada como TERMINADO.\n";
