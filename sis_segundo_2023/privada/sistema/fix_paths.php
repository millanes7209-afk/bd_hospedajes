<?php
require_once('conexion.php');
// Corregir rutas para que tengan el prefijo ../privada/
$db->ejecutar("UPDATE opciones SET contenido = CONCAT('../privada/', contenido) WHERE contenido LIKE 'sistema/%'");
echo "RUTAS_CORREGIDAS: El módulo sistema ahora tiene rutas relativas correctas.\n";
?>
