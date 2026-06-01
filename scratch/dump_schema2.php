<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
try {
    $r = $db->obtenerTodo('SHOW CREATE TABLE habitaciones');
    echo array_values($r[0])[1];
    echo "\n\n";
    $r2 = $db->obtenerTodo('SHOW CREATE TABLE hospedajes');
    echo array_values($r2[0])[1];
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
