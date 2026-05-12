<?php
require_once("conexion.php");
$res = $db->obtenerTodo("DESCRIBE v_movimientos_caja");
foreach($res as $f) {
    echo $f['Field'] . "\n";
}
?>
