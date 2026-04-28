<?php
require_once("conexion.php");
$sql = "INSERT INTO planes (tarea, tipo, estado) VALUES (?, 'CORTO', 'PENDIENTE')";
if ($db->ejecutar($sql, ["Optimización del Mapa (Agrupación por pisos/tipos)"]) !== false) {
    echo "Tarea anotada en la tabla planes con éxito.";
} else {
    echo "Error al anotar la tarea.";
}
?>
