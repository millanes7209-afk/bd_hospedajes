<?php
session_start();
require_once("../../conexion.php");
//require_once("../../libreria_menu.php"); // Esto es opcional si no lo necesitas

//$db->debug=true;

echo "<html> 
       <head>
       </head>
       <body>";


$tipo = $_POST["tipo"];      
$precio = $_POST["precio"];
$descripcion = $_POST["descripcion"];

$reg = array();
$reg["empresaID"] = 1;
$reg["tipo"] = $tipo;
$reg["precio"] = $precio;
$reg["descripcion"] = $descripcion;

$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
$rs1 = $db->AutoExecute("tipo_habitaciones", $reg,"INSERT");
header("Location: tipo_habitaciones.php");
exit();
echo "</body>
      </html>";
?>



