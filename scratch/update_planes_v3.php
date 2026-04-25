<?php
require_once('conexion.php');

$db->ejecutar("INSERT INTO planes (tarea, tipo, estado) VALUES (?, ?, ?)", 
    ['Administrador Global: Estructurar relación técnica entre Contrato, Usuario, Rol y Accesos (Onboarding de empresas)', 'CORTO PLAZO', 'PENDIENTE']);

echo "Plan de Gestión Avanzada de Administrador añadido.\n";
?>
