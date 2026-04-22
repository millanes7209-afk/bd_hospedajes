<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); // Descomenta si es necesario
// $db->debug = true;

$empleadoID = $_POST["empleadoID"];
$apellidos = $_POST["apellidos"];
$nombres = $_POST["nombres"];
$ci = $_POST["ci"];
$telefono = $_POST["telefono"];
$genero = $_POST["genero"];
$fecha_nacimiento = $_POST["fecha_nacimiento"];

$reg = array();
$reg["apellidos"] = $apellidos;
$reg["nombres"] = $nombres;
$reg["ci"] = $ci;
$reg["telefono"] = $telefono;
$reg["genero"] = $genero;
$reg["fecha_nacimiento"] = $fecha_nacimiento;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Actualizar registro en tabla empleados
$rs1 = $db->AutoExecute("empleados", $reg, "UPDATE", "empleadoID = '$empleadoID'");

// Redireccionar después de la actualización
header("Location: personas.php");
exit();
?>
