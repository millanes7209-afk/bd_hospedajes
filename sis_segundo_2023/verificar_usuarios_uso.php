<?php
require 'conexion.php';
echo "--- BUSCANDO VINCULACIÓN DE USUARIOS.PHP ---\n";
$res = $db->obtenerTodo("SELECT * FROM opciones WHERE contenido LIKE '%usuarios.php%'");
if (empty($res)) {
    echo "NO SE ENCONTRÓ NINGUNA OPCIÓN DE MENÚ QUE USE ESTE ARCHIVO.\n";
} else {
    foreach($res as $r) echo "ID: {$r['opcionID']} | Nombre: {$r['opcion']} | Link: {$r['contenido']}\n";
}
?>
