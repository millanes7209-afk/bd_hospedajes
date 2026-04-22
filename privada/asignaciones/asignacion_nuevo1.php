<?php
session_start();
require_once("../../conexion.php");

// Recuperar datos del formulario
$id_persona = $_POST["id_persona"];
$turnoID = $_POST["turnoID"];
$fecha_inicio = $_POST["fecha_inicio"];
$fecha_fin = $_POST["fecha_fin"];
$dias = isset($_POST["dias"]) ? implode(',', $_POST["dias"]) : null;

// Preparar datos para insertar
$reg = array();
$reg["id_persona"] = $id_persona;
$reg["turnoID"] = $turnoID;
$reg["fecha_inicio"] = $fecha_inicio;
$reg["fecha_fin"] = $fecha_fin;
$reg["dias"] = $dias;
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Insertar en la base de datos
$rs1 = $db->AutoExecute("asignaciones", $reg, "INSERT");

// Redirigir al listado de asignaciones
header("Location: asignaciones.php");
exit();
?>
