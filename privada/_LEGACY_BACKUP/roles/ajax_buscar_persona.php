<?php
session_start();
require_once("../../conexion.php");
require_once("../../resaltarBusqueda.inc.php");

$paterno = $_POST["paterno"];
$materno = $_POST["materno"];
$nombres = $_POST["nombres"];
$ci = $_POST["ci"];
$fecha = $_POST["fecha"];

//$db->debug=true;
if ($paterno or $materno or $nombres or $ci or $fecha){
    $sql3 = $db->Prepare("      SELECT  *
                                FROM    personas
                                WHERE   ap LIKE ?
                                AND     am LIKE ?
                                AND     nombres LIKE ?
                                AND     ci LIKE ?
                                AND     _fec_insercion LIKE ?
                                AND     _estado <> 'X'
    ");
    $rs3 = $db->GetAll($sql3, array($paterno."%", $materno."%", $nombres."%", $ci."%", $fecha."%"));
    if ($rs3) {
        echo"<center>
        <table class='listado'>
            <tr>
                <th>C.I.</th><th>PATERNO</th><th>MATERNO</th><th>NOMBRES</th><th>Fecha</th><th><img src='../../imagenes/modificar.gif'></th><th><img src='../../imagenes/borrar.jpeg'></th>
            </tr>";
        foreach ($rs3 as $k => $fila) {
            $str = $fila["ci"];
            $str1 = $fila["ap"];
            $str2 = $fila["am"];
            $str3 = $fila["nombres"];
            $str4 = $fila["_fec_insercion"];

            echo"<tr>
    <td align='center'>".resaltar($ci, $str)."</td>
    <td>".resaltar($paterno, $str1)."</td>
    <td>".resaltar($materno, $str2)."</td>
    <td>".resaltar($nombres, $str3)."</td>
    <td>".resaltar($fecha, $str4)."</td>
    <td align='center'>
        <form name='formModif".$fila["id_persona"]."' method='post' action='persona_modificar.php'>
            <input type='hidden' name='id_persona' value='".$fila['id_persona']."'>
            <a href='javascript:document.formModif".$fila['id_persona'].".submit();' title='Modificar Persona Sistema'>
                Modificar>>
            </a>
        </form>
    </td>
    <td align='center'>
        <form name='formElimi".$fila["id_persona"]."' method='post' action='persona_eliminar.php'>
            <input type='hidden' name='id_persona' value='".$fila["id_persona"]."'>
            <a href='javascript:document.formElimi".$fila['id_persona'].".submit();' title='Eliminar Persona Sistema' onclick='javascript:return(confirm(\"Desea realmente Eliminar a la persona ".$fila["nombres"]." ".$fila["ap"]." ".$fila["am"]." ?\"))'; 
            location.href='persona_eliminar.php''>
                Eliminar>>
            </a>
        </form>
    </td>
</tr>";
        }
    echo"</table>
    </center>";
} else {
    echo"<center><b> LA PERSONA NO EXISTE!!</b></center><br>";
}
}
?>
