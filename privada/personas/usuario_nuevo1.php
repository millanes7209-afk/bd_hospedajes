<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'];

$empleadoID = $_POST["empleadoID"];
$usuario = $_POST["usuario"];
$clave = $_POST["clave"];
$hash=password_hash($clave, PASSWORD_DEFAULT);

   $reg = array();
   $reg["empleadoID"] = $empleadoID;
   $reg["usuario"] = $usuario;
   $reg["clave"] = $hash;
   $reg["_fec_insercion"] = date("Y-m-d H:i:s");
   $reg["_estado"] = 'A';
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("usuarios", $reg, "INSERT"); 
   header("Location: usuarios.php");
   exit();
?>