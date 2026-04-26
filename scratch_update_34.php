<?php
require_once("conexion.php");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 34");
echo "Tarea 34 marcada como TERMINADO.\n";
