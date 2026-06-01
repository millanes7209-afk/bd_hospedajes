<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

$tables = ['ingresos', 'egresos', 'tipo_habitaciones', 'hospedajes_clientes', 'clientes'];

foreach ($tables as $table) {
    try {
        $res = $db->obtenerTodo("SHOW CREATE TABLE $table");
        if ($res && count($res) > 0) {
            $row = array_values($res[0]);
            echo "--- $table ---\n";
            echo $row[1] . "\n\n";
        }
    } catch (Exception $e) {
        echo "Error $table: " . $e->getMessage() . "\n\n";
    }
}
?>
