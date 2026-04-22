<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); por si acaso
//$db->debug=true;
echo"<html> 
       <head>
       <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       <script type='text/javascript' src='../js/expresiones_regulares.js'></script>
       <script type='text/javascript' src='js/validacion_accesos.js'></script>
       </head>
       <body>";
       
$id_rol = $_POST["id_rol"];
$id_acceso = $_POST["id_acceso"];
$id_opcion = $_POST["id_opcion"];


   $reg = array();
   $reg["id_rol"] = $id_rol;
   $reg["id_opcion"] = $id_opcion;   

   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("accesos", $reg, "UPDATE", "id_acceso='".$id_acceso."'");
   header("Location: accesos.php");
   exit();

echo "</body>
      </html> ";
?> 