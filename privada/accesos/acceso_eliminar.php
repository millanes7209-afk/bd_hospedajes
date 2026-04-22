<?php
session_start();

require_once("../../conexion.php");


$__id_acceso = $_REQUEST["id_acceso"];

//$db->debug=true;
echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
/*LAS CONSULTAS SE TIENEN QUE HACER CON TODAS LAS TABLAS EN LAS QUE ID_EMPLEADO ESTA COMO HERENCIA*/
/*$sql = $db->Prepare("SELECT   *
                     FROM     asignaciones
                     WHERE    empleadoID = ?
                     AND      _estado <> 'X'
                   ");
$rs = $db->GetAll($sql, array($__empleadoID));


$sql1 = $db->Prepare("SELECT  *
                     FROM     entregas
                     WHERE    empleadoID = ?
                     AND      _estado <> 'X'
                   ");
$rss = $db->GetAll($sql1, array($__empleadoID));*/

if (!$rs && !$rss) {
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["usuario"] = $_SESSION["sesion_empleadoID"];
    $rs1 = $db->AutoExecute("accesos", $reg, "UPDATE", "id_acceso='".$__id_acceso."'");
    header("Location:accesos.php");
    exit();
    
} else {
    require_once("../../libreria_menu.php");
    echo"<div class='mensaje'>";
        $mensage = "NO SE ELIMINARON LOS DATOS DEL EMPLEADO PORQUE TIENE HERENCIA";
        echo"<h1>".$mensage."</h1>";
        
        echo"<a href='empleados.php'>
                  <input type='button'style='cursor:pointer;border-radius:10px;font-weight:bold;height: 25px;' value='VOLVER>>>>'></input>
             </a>     
            ";
       echo"</div>" ;
}

echo"</body>
</html>";
?>
