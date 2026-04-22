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
       
$id_grupo = $_POST["id_grupo"];
$grupo = $_POST["grupo"];



   $reg = array();
   $reg["grupo"] = $grupo;

   
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("grupos", $reg, "UPDATE", "id_grupo='".$id_grupo."'");
   header("Location: grupos.php");
   exit();
 

echo "</body>
      </html> ";
?> 