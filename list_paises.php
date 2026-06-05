<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");
$res = $db->obtenerTodo("SELECT paisID, nombre FROM paises WHERE _estado <> 'X'");
foreach ($res as $p) {
    echo $p['paisID'] . ": " . $p['nombre'] . "\n";
}
?>