<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); por si acaso
//$db->debug=true;
echo"<html> 
       <head>
       <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       <script type='text/javascript' src='../js/expresiones_regulares.js'></script>
       <script type='text/javascript' src='js/validacion_empleados.js'></script>
       </head>
       <body>";
       
$id_grupo = $_POST["id_grupo"];
$id_opcion = $_POST["id_opcion"];
$opcion = $_POST["opcion"];
$contenido = $_POST["contenido"];
$orden = $_POST["orden"];


   $reg = array();
   $reg["id_grupo"] = $id_grupo;
   $reg["opcion"] = $opcion;   
   $reg["contenido"] = $contenido;
   $reg["orden"] = $orden;

   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("opciones", $reg, "UPDATE", "id_opcion='".$id_opcion."'");
   header("Location: opciones.php");
   exit();

echo "</body>
      </html> ";
?> 