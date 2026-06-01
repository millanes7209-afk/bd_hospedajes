<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

try {
    // Índice para optimizar estadísticas de hospedajes
    $db->ejecutar("ALTER TABLE hospedajes ADD INDEX idx_stats_hospedajes (empresaID, _fec_insercion)");
    echo "Índice en hospedajes creado.\n";
} catch (Exception $e) {
    echo "Info (hospedajes): " . $e->getMessage() . "\n";
}

try {
    // Índice para optimizar estadísticas financieras y la nueva consulta combinada
    $db->ejecutar("ALTER TABLE ingresos ADD INDEX idx_stats_ingresos (empresaID, fecha)");
    echo "Índice en ingresos creado.\n";
} catch (Exception $e) {
    echo "Info (ingresos): " . $e->getMessage() . "\n";
}

?>
