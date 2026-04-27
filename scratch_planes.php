<?php
require 'conexion.php';
print_r($db->obtenerTodo("DESCRIBE planes"));
