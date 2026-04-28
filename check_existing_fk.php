<?php
require_once("conexion.php");
$res = $db->obtenerTodo("SELECT * FROM information_schema.TABLE_CONSTRAINTS 
                        WHERE TABLE_NAME = 'hospedajes_auditoria_montos' 
                        AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
print_r($res);
?>
