<?php
session_start();
require_once("../../conexion.php");

//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
       


$cargo = $_POST["cargo"];
$descripcion = $_POST["descripcion"];



   $reg = array();
   $reg["empresaID"] = 1;
   $reg["cargo"] = $cargo;
   $reg["descripcion"] = $descripcion;
   
   $reg["_fec_insercion"] = date("Y-m-d H:i:s");
   $reg["_estado"] = 'A';
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("cargos", $reg, "INSERT"); 
   header("Location: cargos.php");
   exit();



echo "</body>
      </html> ";
?> 