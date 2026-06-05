<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

echo "--- LISTADO DE TRIGGERS ---\n";
$triggers = $db->obtenerTodo("SHOW TRIGGERS");
foreach ($triggers as $t) {
    echo "Nombre: {$t['Trigger']}\n";
    echo "Tabla: {$t['Table']}\n";
    echo "Evento: {$t['Event']}\n";
    echo "Momento: {$t['Timing']}\n";
    echo "Sentencia: \n{$t['Statement']}\n";
    echo "---------------------------\n";
}
?>