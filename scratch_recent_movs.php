<?php
require 'conexion.php';
echo "--- ALL MOVEMENTS ---\n";
print_r($db->obtenerTodo("SELECT * FROM movimientos ORDER BY movimientoID DESC LIMIT 10"));
