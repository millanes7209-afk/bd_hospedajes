<?php
session_start();

require_once("../../conexion.php");


$__id_opcion = $_REQUEST["id_opcion"];

//$db->debug=true;
echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>";
/*LAS CONSULTAS SE TIENEN QUE HACER CON TODAS LAS TABLAS EN LAS QUE ID_EMPLEADO ESTA COMO HERENCIA*/
$sql = $db->Prepare("SELECT   *
                     FROM     accesos
                     WHERE    id_opcion = ?
                     AND      _estado <> 'X'
                   ");
$rs = $db->GetAll($sql, array($__id_opcion));

if (!$rs) {
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["usuario"] = $_SESSION["sesion_id_opcion"];
    $rs1 = $db->AutoExecute("opciones", $reg, "UPDATE", "id_opcion='".$__id_opcion."'");
    header("Location:opciones.php");
    exit();
    
} else {
    require_once("../../libreria_menu.php");
    echo"<div class='mensaje'>";
        $mensage = "NO SE ELIMINARON LOS DATOS DE LA OPCION PORQUE TIENE HERENCIA";
        echo"<h1>".$mensage."</h1>";
        
        echo"<a href='opciones.php'>
                  <input type='button'style='cursor:pointer;border-radius:10px;font-weight:bold;height: 25px;' value='VOLVER>>>>'></input>
             </a>     
            ";
       echo"</div>" ;
}

echo"</body>
</html>";
?>
