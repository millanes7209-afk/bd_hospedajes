<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

function showIndexes($db, $table)
{
    echo "=== Índices de la tabla: $table ===\n";
    $res = $db->obtenerTodo("SHOW INDEX FROM $table");
    foreach ($res as $r) {
        echo "- Columna: {$r['Column_name']} (Indice: {$r['Key_name']}, Tipo: {$r['Index_type']})\n";
    }
    echo "\n";
}

showIndexes($db, 'clientes');
showIndexes($db, 'hospedajes');
showIndexes($db, 'hospedajes_clientes');
?>