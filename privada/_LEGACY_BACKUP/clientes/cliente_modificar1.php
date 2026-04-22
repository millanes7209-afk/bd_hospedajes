<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos enviados por el formulario
$clienteID = $_POST["clienteID"];
$ci = $_POST["ci"];
$nombres = $_POST["nombres"];
$apellidos = $_POST["apellidos"];
$fecha_nacimiento = $_POST["fecha_nacimiento"];
$lugar_nacimiento = $_POST["lugar_nacimiento"];
$est_civil = $_POST["est_civil"];
$profesion = $_POST["profesion"];

// Datos para actualizar en la base de datos
$reg = array();
$reg["ci"] = $ci;
$reg["nombres"] = $nombres;
$reg["apellidos"] = $apellidos;
$reg["fecha_nacimiento"] = $fecha_nacimiento;
$reg["lugar_nacimiento"] = $lugar_nacimiento;
$reg["est_civil"] = $est_civil;
$reg["profesion"] = $profesion;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Actualizar el cliente en la base de datos
$rs1 = $db->AutoExecute("clientes", $reg, "UPDATE", "clienteID='".$clienteID."'");

// Redirigir al listado de clientes
header("Location: clientes.php");
exit();
?>
