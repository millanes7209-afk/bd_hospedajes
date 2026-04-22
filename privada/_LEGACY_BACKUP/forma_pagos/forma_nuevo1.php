<?php
session_start();
require_once("../../conexion.php");
//$db->debug=true;
echo"<html> 
       <head>
        
       </head>
       <body>";
       


$tipo = $_POST["tipo"];
$descripcion = $_POST["descripcion"];



   $reg = array();
   $reg["empresaID"] = 1;
   $reg["tipo"] = $tipo;
   $reg["descripcion"] = $descripcion;
   
   $reg["_fec_insercion"] = date("Y-m-d H:i:s");
   $reg["_estado"] = 'A';
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("formas_pago", $reg, "INSERT"); 
   header("Location: formas_pago.php");
   exit();



echo "</body>
      </html> ";
?> 