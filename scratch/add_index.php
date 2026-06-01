<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
try {
    $db->ejecutar("ALTER TABLE hospedajes ADD INDEX idx_estado_checkout (estado, checkout)");
    echo "Índice (estado, checkout) creado con éxito.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
