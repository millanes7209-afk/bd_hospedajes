<?php
session_start();
$_SESSION['sesion_id_usuario'] = 1;
$_SESSION['sesion_rol'] = 'PROPIETARIO';
$_SESSION['empresaID'] = 1;

$_GET['inicio'] = '2020-01-01';
$_GET['fin'] = '2030-01-01';

require_once('privada/movimientos/ajax_estadisticas.php');
?>
