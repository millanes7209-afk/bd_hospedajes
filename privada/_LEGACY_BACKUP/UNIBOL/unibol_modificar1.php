<?php
session_start();
require_once("../../conexion.php");
//require_once("../../libreria_menu.php"); // Esto es opcional si no lo necesitas

//$db->debug=true;

echo "<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";

$empresaID = $_POST["empresaID"];
$nombre = $_POST["nombre"];      
$direccion = $_POST["direccion"];
$telefono = $_POST["telefono"];

$logo_hidden = $_POST["logo_hidden"];

$url="C:/wamp64/www/unibol/img/";





if($_FILES["logo_agencia"]["name"]!=""){
  
   
   $nuevo = $url . basename($_FILES["logo_agencia"]["name"]);

    move_uploaded_file($_FILES["logo_agencia"]["tmp_name"], $nuevo);
    
    $logo_agencia=$_FILES["logo_agencia"]["name"];
}else{
   $logo_agencia=$logo_hidden;
} 

$reg = array();
$reg["direccion"] = $direccion;
$reg["telefono"] = $telefono;
$reg["nombre"] = $nombre;
$reg["logo_agencia"] = $logo_agencia;

$reg["_usuario"] = $_SESSION["sesion_id_usuario"];   
$rs1 = $db->AutoExecute("emp_mensajeria", $reg, "UPDATE", "empresaID='".$empresaID."'");
header("Location:unibol_modificar.php");
exit();
echo "</body>
      </html>";
?>



