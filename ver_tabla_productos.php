<?php
require_once("conexion.php");

$sql = "DESCRIBE productos";
try {
    $columnas = $db->obtenerTodo($sql);
    echo "<h3>Estructura de tabla productos:</h3>";
    echo "<pre>";
    print_r($columnas);
    echo "</pre>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
