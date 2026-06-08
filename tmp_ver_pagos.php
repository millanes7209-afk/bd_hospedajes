<?php
require 'conexion.php';
echo "=== COLUMNAS DE INGRESO_PAGOS ===\n";
$desc = $db->obtenerTodo("DESCRIBE ingreso_pagos");
foreach ($desc as $d)
    echo "{$d['Field']} ({$d['Type']})\n";
