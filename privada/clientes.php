<?php
/**
 * Punto de entrada para el módulo de clientes (MVC)
 */

require_once 'controllers/ClienteController.php';

// Crear y ejecutar el controlador
$controller = new ClienteController();
$controller->execute();
?>
