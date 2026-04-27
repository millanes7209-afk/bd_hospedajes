<?php
require 'conexion.php';

echo "--- TRIGGERS EN LA BD ---\n";
print_r($db->obtenerTodo("SHOW TRIGGERS"));
