<?php
session_start();
require_once("../../conexion.php");
// require_once("../../libreria_menu.php"); // Esto es por si acaso

//$db->debug=true;

echo "<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";

$id_rol = $_POST["id_rol"];
$rol = $_POST["rol"];

$reg = array();
$reg["rol"] = $rol;

$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$rs1 = $db->AutoExecute("roles", $reg, "UPDATE", "id_rol='".$id_rol."'");
header("Location: roles.php");
exit();

echo "</body>
      </html>";
?>
