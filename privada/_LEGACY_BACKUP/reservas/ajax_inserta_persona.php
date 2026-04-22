<?php
session_start();
require_once("../../conexion.php");


$ci1=$_POST["ci1"];
$apellidos1=$_POST["apellidos1"];
$nombres1=$_POST["nombres1"];
$fecha_nacimiento1=$_POST["fecha_nacimiento1"];
$lugar_nacimiento1=$_POST["lugar_nacimiento1"];
$estado_civil1=$_POST["estado_civil1"];
$profesion1=$_POST["profesion1"];

$reg = array();
$reg["empresaID"] = 1;
$reg["ci"] = $ci1;
$reg["apellidos"] = $apellidos1;
$reg["nombres"] = $nombres1;
$reg["fecha_nacimiento"] = $fecha_nacimiento1;
$reg["lugar_nacimiento"] = $lugar_nacimiento1;
$reg["est_civil"] = $estado_civil1;
$reg["profesion"] = $profesion1;


$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
$rs1 = $db->AutoExecute("clientes", $reg, "INSERT"); 
?>