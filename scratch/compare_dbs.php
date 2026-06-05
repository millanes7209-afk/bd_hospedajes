<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

echo "=== TABLAS EN bd_hospedajes (NUEVA) ===\n";
$tablas_new = $db->obtenerTodo("SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'bd_hospedajes' ORDER BY TABLE_NAME");
foreach ($tablas_new as $t) {
    echo sprintf("%-40s %6d filas  %10d bytes\n", $t['TABLE_NAME'], $t['TABLE_ROWS'], $t['DATA_LENGTH']);
}

echo "\n=== TABLAS EN bd_dulces (ANTIGUA) ===\n";
$tablas_old = $db->obtenerTodo("SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'bd_dulces' ORDER BY TABLE_NAME");
foreach ($tablas_old as $t) {
    echo sprintf("%-40s %6d filas  %10d bytes\n", $t['TABLE_NAME'], $t['TABLE_ROWS'], $t['DATA_LENGTH']);
}

echo "\n=== TABLAS SOLO EN bd_dulces (a migrar?) ===\n";
$nombres_new = array_column($tablas_new, 'TABLE_NAME');
$nombres_old = array_column($tablas_old, 'TABLE_NAME');
$solo_old = array_diff($nombres_old, $nombres_new);
$solo_new = array_diff($nombres_new, $nombres_old);
$comunes = array_intersect($nombres_old, $nombres_new);

foreach ($solo_old as $t)
    echo "  [SOLO bd_dulces]     $t\n";
echo "\n=== TABLAS SOLO EN bd_hospedajes ===\n";
foreach ($solo_new as $t)
    echo "  [SOLO bd_hospedajes] $t\n";
echo "\n=== TABLAS EN COMUN ===\n";
foreach ($comunes as $t)
    echo "  [COMUN]              $t\n";
?>