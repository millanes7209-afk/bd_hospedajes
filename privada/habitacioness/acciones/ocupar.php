<?php

require_once("../../conexion.php");

$numero = $_GET['numero'];
$tipo = $_GET['tipo'];
$precio = $_GET['precio'];
$habitacionID = $_GET['habitacionID'];
$accion = $_GET['accion'];

if ($accion === 'hospedar') {
    // Redirigir al formulario de hospedaje
    header("Location: ../../hospedajes/hospedaje_nuevo.php?habitacionID=$habitacionID&numero=$numero&precio=$precio");
} elseif ($accion === 'reservar') {
    // Redirigir al formulario de reserva
    header("Location: ../../reservas/reserva_nuevo.php?habitacionID=$habitacionID&numero=$numero&precio=$precio");
}
?>
