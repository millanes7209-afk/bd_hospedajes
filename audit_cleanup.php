<?php
require_once('conexion.php');

echo "=== GRUPOS ACTIVOS ===\n";
$grupos = $db->obtenerTodo("SELECT * FROM grupos WHERE _estado <> 'X'");
foreach($grupos as $g) {
    echo "ID: {$g['grupoID']} | Nombre: {$g['grupo']}\n";
}

echo "\n=== OPCIONES ACTIVAS (RUTAS) ===\n";
$opciones = $db->obtenerTodo("SELECT o.opcion, o.contenido, g.grupo 
                              FROM opciones o 
                              JOIN grupos g ON o.grupoID = g.grupoID 
                              WHERE o._estado <> 'X'");
foreach($opciones as $o) {
    echo "[{$o['grupo']}] -> {$o['opcion']} | Path: {$o['contenido']}\n";
}

echo "\n=== DIRECTORIOS EN PRIVADA ===\n";
$dir = 'privada/';
$folders = array_filter(glob($dir . '*'), 'is_dir');
foreach($folders as $f) {
    echo str_replace($dir, '', $f) . "\n";
}
?>
