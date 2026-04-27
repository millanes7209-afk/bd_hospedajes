<?php
require 'conexion.php';
print_r($db->obtenerTodo("SELECT * FROM formas_pago"));
