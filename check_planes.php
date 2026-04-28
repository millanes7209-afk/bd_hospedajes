<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SHOW TABLES LIKE 'planes%'");
print_r($res);
if(count($res) > 0){
    $tabla = array_values($res[0])[0];
    echo "\nESTRUCTURA DE $tabla:\n";
    print_r($db->obtenerTodo("DESCRIBE $tabla"));
}
?>
