<?php
require_once("../conexion.php");
$res = $db->obtenerFila("SHOW CREATE VIEW v_movimientos_caja");
file_put_contents("view_def.txt", $res['Create View']);
echo "DONE";
?>