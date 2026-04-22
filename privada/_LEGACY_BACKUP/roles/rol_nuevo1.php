<?php
session_start();
require_once("../../conexion.php");

//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";

$rol = $_POST["rol"];

$reg = array();
$reg["rol"] = $rol;
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$rs1 = $db->AutoExecute("roles", $reg, "INSERT"); 
header("Location: roles.php");
exit();

echo "</body>
      </html>";
?> 
