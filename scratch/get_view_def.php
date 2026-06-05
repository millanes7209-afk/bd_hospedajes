<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
$res = $db->obtenerTodo("SHOW CREATE VIEW v_movimientos_caja");
if ($res) {
    echo array_values($res[0])[1];
} else {
    echo "Vista no encontrada.";
}
?>