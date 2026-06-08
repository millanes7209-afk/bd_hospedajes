<?php
require 'conexion.php';
echo "=== COLUMNAS DE INGRESOS ===\n";
$desc = $db->obtenerTodo("DESCRIBE ingresos");
foreach ($desc as $d)
    echo "{$d['Field']} ({$d['Type']})\n";
