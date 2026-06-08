<?php
require 'conexion.php';

echo "=== TODAS LAS FUNCIONALIDADES ===\n";
$func = $db->obtenerTodo("SELECT funcionalidadID, nombre FROM funcionalidades WHERE _estado <> 'X'");
foreach ($func as $f) {
    echo "ID: {$f['funcionalidadID']} -> {$f['nombre']}\n";
}

echo "=== OPCIONES ASIGNADAS AL MÓDULO 4 (BAÑOS) ===\n";
$opciones4 = $db->obtenerTodo("SELECT opcion, contenido FROM opciones WHERE funcionalidadID = 4 AND _estado <> 'X'");
foreach ($opciones4 as $o)
    echo " - {$o['opcion']} ({$o['contenido']})\n";

echo "\n=== OPCIONES ASIGNADAS AL MÓDULO 2 (PERSONAL/FINANZAS) ===\n";
$opciones2 = $db->obtenerTodo("SELECT opcion, contenido FROM opciones WHERE funcionalidadID = 2 AND _estado <> 'X'");
foreach ($opciones2 as $o)
    echo " - {$o['opcion']} ({$o['contenido']})\n";
