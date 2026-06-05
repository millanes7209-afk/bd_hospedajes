<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
$res = $db->obtenerTodo("SHOW DATABASES");
foreach ($res as $db_row) {
    echo $db_row['Database'] . "\n";
}
?>