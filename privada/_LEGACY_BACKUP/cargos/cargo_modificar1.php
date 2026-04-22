<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); esto es por si acaso

//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
       
$cargoID = $_POST["cargoID"];
$cargo = $_POST["cargo"];
$descripcion = $_POST["descripcion"];



   $reg = array();
   $reg["empresaID"] = 1;
   $reg["cargo"] = $cargo;
   $reg["descripcion"] = $descripcion;
   
   
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("cargos", $reg, "UPDATE", "cargoID='".$cargoID."'");
   header("Location: cargos.php");
   exit();


echo "</body>
      </html> ";
?> 