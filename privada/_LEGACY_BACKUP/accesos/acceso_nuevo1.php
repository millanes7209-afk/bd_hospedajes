<?php
session_start();
require_once("../../conexion.php");

$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
       


$id_opcion = $_POST["id_opcion"];
$id_rol = $_POST["id_rol"];

   $reg = array();
   $reg["id_opcion"] = $id_opcion;
   $reg["id_rol"] = $id_rol;

   $reg["_fec_insercion"] = date("Y-m-d H:i:s");
   $reg["_estado"] = 'A';
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("accesos", $reg, "INSERT"); 
   header("Location: accesos.php");
   exit();



echo "</body>
      </html> ";
?> 