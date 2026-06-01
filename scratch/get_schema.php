<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

function printTable($db, $name) {
    try {
        $res = $db->obtenerTodo("SHOW CREATE TABLE $name");
        if ($res && count($res) > 0) {
            $row = array_values($res[0]);
            echo "--- $name ---\n";
            echo $row[1] . "\n\n";
        }
    } catch (Exception $e) {
        echo "Error $name: " . $e->getMessage() . "\n";
    }
}

printTable($db, 'habitaciones');
printTable($db, 'hospedajes');
?>
