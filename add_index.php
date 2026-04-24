<?php
require_once("conexion.php");
try {
    $db->ejecutar("ALTER TABLE empleados ADD UNIQUE INDEX idx_ci_unico (ci)");
    echo "INDICE CREADO EXITOSAMENTE";
} catch (Exception $e) {
    echo "ERROR O EL INDICE YA EXISTIA: " . $e->getMessage();
}
?>
