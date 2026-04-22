<?php
session_start();
require_once("../../conexion.php");
//require_once("../../libreria_menu.php");
//$db->debug=true;

$empresaID = $_SESSION['empresaID'];
$empleadoID = $_POST["empleadoID"];
$usuarioID = $_POST["usuarioID"];
$usuario = $_POST["usuario"];
$clave = $_POST["clave"];
$hash=password_hash($clave, PASSWORD_DEFAULT);

   $reg = array();
   $reg["empleadoID"] = $empleadoID;
   $reg["usuario"] = $usuario;
   $reg["clave"] = $hash;   
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("usuarios", $reg, "UPDATE", "usuarioID='".$usuarioID."'");
   header("Location: usuarios.php");
   exit();
?>