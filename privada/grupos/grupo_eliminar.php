<?php
session_start();
require_once("../../conexion.php");

$__id_grupo = $_REQUEST["id_grupo"];

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
//$db->debug=true;

/*LAS CONSULTAS SE TIENEN QUE HACER CON TODAS LAS TABLAS EN LAS QUE id_grupo ESTA COMO HERENCIA*/
$sql = $db->Prepare("SELECT *
                     FROM   opciones
                     WHERE  id_grupo = ?
                     AND    _estado <> 'X'
                   ");
$rs = $db->GetAll($sql, array($__id_grupo));


if (!$rs) {
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("grupos", $reg, "UPDATE", "id_grupo='".$__id_grupo."'");
    header("Location:grupos.php");
    exit();
    
} else {
    require_once("../../libreria_menu.php");
     echo"<div class='mensaje'>";
        $mensage = "NO SE ELIMINARON LOS DATOS DEL GRUPO PORQUE TIENE HERENCIA";
        echo"<h1>".$mensage."</h1>";
        
        echo"<a href='grupos.php'>
                  <input type='button'style='cursor:pointer;border-radius:10px;font-weight:bold;height: 25px;' value='VOLVER>>>>'></input>
             </a>     
            ";
       echo"</div>" ;
}


echo"</body>
</html>";
?>
