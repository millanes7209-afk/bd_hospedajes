<?php
session_start();
require_once("../../conexion.php");

//$db->debug=true;

echo"<html> 
       <head>
       </head>
       <body>";

$tipo = $_POST["tipo"];
$hora_inicio = $_POST["hora_inicio"];
$hora_fin = $_POST["hora_fin"];
$descripcion = $_POST["descripcion"];

$reg = array();
$reg["empresaID"] = 1; // El valor fijo de la empresa, como mencionaste
$reg["tipo"] = $tipo;
$reg["hora_inicio"] = $hora_inicio;
$reg["hora_fin"] = $hora_fin;
$reg["descripcion"] = $descripcion;

$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A'; // Estado fijo
$reg["_usuario"] = $_SESSION["sesion_id_usuario"]; // Usuario de sesión

$rs1 = $db->AutoExecute("turnos", $reg, "INSERT"); 

header("Location: turnos.php");
exit();

echo "</body>
      </html> ";
?>
