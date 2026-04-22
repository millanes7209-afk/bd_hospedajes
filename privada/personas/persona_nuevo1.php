<?php
session_start();
require_once("../../conexion.php");

//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
       

$cargoID = $_POST["cargoID"];
$ap = $_POST["ap"];
$am = $_POST["am"];
$nombres = $_POST["nombres"];
$ci = $_POST["ci"];
$direccion = $_POST["direccion"];
$telefono = $_POST["telefono"];
$sueldo = $_POST["sueldo"];
$fecha_contratacion = $_POST["fecha_contratacion"];
$genero1 = isset($_POST["genero"]); 


   $reg = array();
   $reg["empresaID"] = 1;
   $reg["cargoID"] = $cargoID;
   $reg["ap"] = $ap;
   $reg["am"] = $am;
   $reg["nombres"] = $nombres;
   $reg["ci"] = $ci;
   $reg["direccion"] = $direccion;
   $reg["telefono"] = $telefono;
   $reg["sueldo"] = $sueldo;
   $reg["fecha_contratacion"] = $fecha_contratacion;
   $reg["genero"] = $_POST["genero"];
   
   $reg["_fec_insercion"] = date("Y-m-d H:i:s");
   $reg["_estado"] = 'A';
   $reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
   $rs1 = $db->AutoExecute("personas", $reg, "INSERT"); 
   header("Location: personas.php");
   exit();



echo "</body>
      </html> ";
?> 