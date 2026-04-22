<?php
session_start();
require_once("../../conexion.php");

// Recuperar datos del formulario
$turnoID = $_POST["turnoID"];
$id_persona = $_POST["id_persona"];
$id_usuario = $_POST["id_usuario"];
$aceptado = isset($_POST["aceptado"]) ? 1 : 0;
$fecha_aceptacion = $_POST["fecha_aceptacion"] ? $_POST["fecha_aceptacion"] : null;

// Preparar datos para insertar
$reg = array();
$reg["turnoID"] = $turnoID;
$reg["id_persona"] = $id_persona;
$reg["id_usuario"] = $id_usuario;
$reg["aceptado"] = $aceptado;
$reg["fecha_aceptacion"] = $fecha_aceptacion;
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Insertar en la base de datos
$rs1 = $db->AutoExecute("suplencias", $reg, "INSERT");

// Redirigir al listado de suplencias
header("Location: suplencias.php");
exit();
?>
