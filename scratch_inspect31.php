<?php
require 'conexion.php';

echo "--- HOSPEDAJE 31 ---\n";
print_r($db->obtenerTodo("SELECT * FROM hospedajes WHERE hospedajeID = 31"));

echo "\n--- MOVIMIENTOS REF 31 ---\n";
print_r($db->obtenerTodo("SELECT * FROM movimientos WHERE referenciaID = 31"));
