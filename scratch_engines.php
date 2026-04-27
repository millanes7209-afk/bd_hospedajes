<?php
require 'conexion.php';
$rs = $db->obtenerTodo("SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'bd_hospedajes'");
print_r($rs);
