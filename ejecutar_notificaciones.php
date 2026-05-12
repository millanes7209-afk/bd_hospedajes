<?php
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Definir el archivo que generará las notificaciones
$archivo_notificaciones = 'crear_notificaciones.php';

// Comprobar si ya se ha ejecutado hoy
$result = $db->obtenerTodo($query);
 
// Si no hay notificaciones creadas hoy, ejecutamos el archivo
if (count($result) == 0) {
    // Ejecutar el archivo PHP que genera las notificaciones
    include($archivo_notificaciones);
    echo "Notificaciones creadas correctamente.";
} else {
    echo "Las notificaciones ya fueron generadas hoy.";
}
?>
