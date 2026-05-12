<?php
require_once("conexion.php");
echo "Hora PHP: " . date("Y-m-d H:i:s") . "\n";
$res = $db->obtenerFila("SELECT NOW() as hora_db");
echo "Hora DB:  " . $res['hora_db'] . "\n";
?>
