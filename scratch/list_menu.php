<?php
require_once('conexion.php');
$grupos = $db->obtenerTodo("SELECT * FROM grupos");
foreach($grupos as $g) {
    echo "GRUPO [".$g['grupoID']."]: ".$g['grupo']."\n";
    $opciones = $db->obtenerTodo("SELECT * FROM opciones WHERE grupoID = ?", [$g['grupoID']]);
    foreach($opciones as $o) {
        echo "  - ".$o['opcion']." (".$o['contenido'].")\n";
    }
}
?>
