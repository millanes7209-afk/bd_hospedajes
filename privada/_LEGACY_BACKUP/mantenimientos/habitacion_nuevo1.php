<?php
session_start();
require_once("../../conexion.php");
//require_once("../../libreria_menu.php"); // Esto es opcional si no lo necesitas

//$db->debug=true;

echo "<html> 
       <head>
       </head>
       <body>";

$tipohabitacionID = $_POST["tipohabitacionID"];
$numero = $_POST["numero"];      
$descripcion = $_POST["descripcion"];
$tv = $_POST["tv"];
$bano = $_POST["bano"];
$ventilador = $_POST["ventilador"];

$reg = array();
$reg["numero"] = $numero;
$reg["tipohabitacionID"] = $tipohabitacionID;
$reg["estado"] = "DISPONIBLE";
$reg["descripcion"] = $descripcion;
$reg["tv"] = $tv;
$reg["bano"] = $bano;
$reg["ventilador"] = $ventilador;

$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
$rs1 = $db->AutoExecute("habitaciones", $reg,"INSERT");
header("Location: habitaciones.php");
exit();
echo "</body>
      </html>";
?>



