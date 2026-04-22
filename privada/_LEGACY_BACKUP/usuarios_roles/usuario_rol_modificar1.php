<?php
session_start();
require_once("../../conexion.php");

// Obtener datos del formulario
$id_usuario_rol = $_POST["id_usuario_rol"];
$id_usuario = $_POST["id_usuario"];
$id_rol = $_POST["id_rol"];

// Preparar datos para la actualización
$reg = array();
$reg["id_usuario"] = $id_usuario;
$reg["id_rol"] = $id_rol;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Ejecutar la actualización
$rs1 = $db->AutoExecute("usuarios_roles", $reg, "UPDATE", "id_usuario_rol='".$id_usuario_rol."'");

// Redireccionar a la lista de relaciones usuario-rol
header("Location: usuarios_roles.php");
exit();
?>
