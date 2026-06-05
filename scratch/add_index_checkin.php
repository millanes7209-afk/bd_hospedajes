<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';
try {
    $db->ejecutar("ALTER TABLE hospedajes ADD INDEX idx_hospedajes_checkin (checkin)");
    echo "Índice 'idx_hospedajes_checkin' creado correctamente.\n";
} catch (Exception $e) {
    echo "El índice ya existe o hubo un error: " . $e->getMessage() . "\n";
}
?>