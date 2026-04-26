<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 20");
echo "Tarea 20 marcada como TERMINADO.\n";
