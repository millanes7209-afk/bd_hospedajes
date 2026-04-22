<?php
session_start();

// Incluir archivo de conexión
require_once 'conexion.php';

// Definir el archivo que generará las notificaciones
$archivo_notificaciones = 'crear_notificaciones.php';

// Comprobar si ya se ha ejecutado hoy
$query = "SELECT * FROM notificaciones WHERE _fec_insercion >= CURDATE() LIMIT 1";
$result = $db->Execute($query);

// Si no hay notificaciones creadas hoy, ejecutamos el archivo
if ($result->RecordCount() == 0) {
    // Ejecutar el archivo PHP que genera las notificaciones
    include($archivo_notificaciones);
    echo "Notificaciones creadas correctamente.";
} else {
    echo "Las notificaciones ya fueron generadas hoy.";
}
?>
