<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos enviados desde el formulario
$id_usuario = $_POST["id_usuario"];
$id_rol = $_POST["id_rol"];

// Preparar el arreglo para insertar los datos
$reg = array();
$reg["id_usuario"] = $id_usuario;
$reg["id_rol"] = $id_rol;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_fec_modificacion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';

// Ejecutar la inserción en la base de datos
$rs1 = $db->AutoExecute("usuarios_roles", $reg, "INSERT");

// Redireccionar al archivo que muestra la lista de relaciones usuario-rol
header("Location: usuarios_roles.php");
exit();
?>
