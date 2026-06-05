<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
$res = $db->obtenerTodo("SHOW CREATE VIEW v_movimientos_caja");
if ($res) {
    file_put_contents('c:/xampp/htdocs/dulces/sis_segundo_2023/scratch/view_full_def.txt', array_values($res[0])[1]);
    echo "Definición guardada en view_full_def.txt";
}
?>