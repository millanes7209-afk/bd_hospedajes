<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
try {
    echo "Añadiendo índices a la tabla clientes...\n";
    $db->ejecutar("ALTER TABLE clientes ADD INDEX idx_clientes_nombres (nombres)");
    $db->ejecutar("ALTER TABLE clientes ADD INDEX idx_clientes_apellidos (apellido1, apellido2)");
    echo "Índices creados con éxito.\n";
} catch (Exception $e) {
    echo "Aviso: " . $e->getMessage() . "\n";
}
?>