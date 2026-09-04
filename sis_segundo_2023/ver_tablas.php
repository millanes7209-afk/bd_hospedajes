<?php
require_once("conexion.php");

$sql = "SHOW TABLES";
try {
    $tablas = $db->obtenerTodo($sql);
    echo "<h3>Tablas en la base de datos:</h3>";
    echo "<pre>";
    print_r($tablas);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
