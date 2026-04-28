<?php
require_once("conexion.php");
// Buscar la opción de formas de pago
$opcion = $db->obtenerFila("SELECT opcionID FROM opciones WHERE contenido LIKE '%formas_pago.php%'");
if ($opcion) {
    $id = $opcion['opcionID'];
    echo "ID Opción: $id\n";
    // Ver si hay un acceso para el rol 1
    $acceso = $db->obtenerFila("SELECT * FROM accesos WHERE opcionID = ? AND rolID = 1", [$id]);
    print_r($acceso);
} else {
    echo "No se encontró la opción formas_pago.php en la tabla opciones.";
}
?>
