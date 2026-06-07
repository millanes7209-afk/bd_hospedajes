<?php
require_once 'conexion.php';
$tablas = $db->obtenerTodo("SHOW TABLES");
foreach ($tablas as $t) {
    print_r($t);
}
