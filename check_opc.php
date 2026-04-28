<?php
require_once("conexion.php");
print_r($db->obtenerTodo("DESCRIBE opciones"));
?>
