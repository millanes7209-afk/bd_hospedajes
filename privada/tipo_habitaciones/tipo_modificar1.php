<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); esto es por si acaso

//$db->debug=true;

echo"<html> 
       <head>

       </head>
       <body>";
       
$tipohabitacionID = $_POST["tipohabitacionID"];
$tipo = $_POST["tipo"];
$precio = $_POST["precio"];
$descripcion = $_POST["descripcion"];



$reg = array();
$reg["empresaID"] = 1;
$reg["tipo"] = $tipo;
$reg["precio"] = $precio;
$reg["descripcion"] = $descripcion;


$reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
$rs1 = $db->AutoExecute("tipo_habitaciones", $reg, "UPDATE", "tipohabitacionID='".$tipohabitacionID."'");
header("Location: tipo_habitaciones.php");
exit();


echo "</body>
      </html> ";
?> 