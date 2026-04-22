<?php
require_once("../../conexion.php");
echo "TABLE: habitaciones\n";
$rs = $db->Execute("DESCRIBE habitaciones");
if ($rs) while($row = $rs->fetch()) echo "Field: ".$row['Field']."\n";

echo "\nTABLE: tipo_habitaciones\n";
$rs = $db->Execute("DESCRIBE tipo_habitaciones");
if ($rs) while($row = $rs->fetch()) echo "Field: ".$row['Field']."\n";
?>
