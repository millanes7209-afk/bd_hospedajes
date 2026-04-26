<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 26");
echo "Tarea 26 marcada como TERMINADO.\n";
