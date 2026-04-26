<?php
require_once("conexion.php");

$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 24");
$db->ejecutar("UPDATE planes SET estado = 'TERMINADO' WHERE planID = 27");

echo "Estados actualizados correctamente.\n";
