<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
$res = $db->obtenerTodo("SHOW TABLES");
foreach ($res as $row) {
    echo array_values($row)[0] . "\n";
}
