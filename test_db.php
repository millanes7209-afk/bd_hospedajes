<?php
session_start();
$_SESSION['empresaID'] = 1; // Assuming 1 for testing
$_SESSION['sesion_id_usuario'] = 1;

require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");

echo "Testing connection...\n";
try {
    $sql = "SELECT hab.habitacionID, hab.estado, hab.numero
            FROM habitaciones hab 
            WHERE hab._estado <> 'X' 
            AND hab.empresaID = ?
            LIMIT 1";
    $rs = $db->obtenerTodo($sql, [1]);
    print_r($rs);
} catch (Exception $e) {
    echo "Caught: " . $e->getMessage() . "\n";
}
?>