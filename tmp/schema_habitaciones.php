<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
$res = $db->obtenerTodo("DESCRIBE habitaciones");
foreach ($res as $row) {
    echo $row['Field'] . "\n";
}
