<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos enviados por el formulario
$habitacionID = $_POST["habitacionID"];
$numero = $_POST["numero"];

$tv = isset($_POST["tv"]) ? 1 : 0;
$bano = isset($_POST["bano"]) ? 1 : 0;

// Datos para actualizar en la base de datos
$reg = array();
$reg["numero"] = $numero;

$reg["tv"] = $tv;
$reg["bano"] = $bano;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Actualizar la habitación en la base de datos
$rs1 = $db->AutoExecute("habitaciones", $reg, "UPDATE", "habitacionID='".$habitacionID."'");

// Redirigir al listado de habitaciones
header("Location: habitaciones.php");
exit();
?>
