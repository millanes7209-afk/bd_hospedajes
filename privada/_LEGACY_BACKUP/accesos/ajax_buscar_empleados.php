<?php
session_start();
require_once("../../conexion.php");

require_once("../../resaltarBusqueda.inc.php");
//$db->debug=true;
$cargo=strip_tags(stripslashes($_POST["cargo"]));
$apellidos=strip_tags(stripslashes($_POST["apellidos"]));
$nombres=strip_tags(stripslashes($_POST["nombres"]));



if($cargo or $apellidos or $nombres){
           $sql3 = $db->Prepare(" SELECT     *
                                  FROM       cargos car, empleados emp
                                  WHERE      car.cargo LIKE ?
                                  AND        emp.apellidos LIKE ?
                                  AND        emp.nombres LIKE ?
                                  AND        emp.cargoID=car.cargoID
                                  AND        car._estado <> 'X' 
                                  AND        emp._estado <> 'X'               
                              ");
$rs3 = $db->GetAll($sql3,array($cargo."%",$apellidos."%",$nombres."%"));
if ($rs3) {
  echo"<center>
        <table class='listado'>
          <tr>                                   
            <th>CARGO</th><th>APELLIDOS</th><th>NOMBRES</th>
            <th><img src='../../imagenes/modificar.gif'></th>
            <th><img src='../../imagenes/borrar.jpeg'></th>
          </tr>";
      foreach ($rs3 as $k => $fila) {                                       
                  $str=$fila["cargo"];                  
                  $str1=$fila["apellidos"];
                  $str2=$fila["nombres"];
          echo "<tr>
                    <td align='center'>".resaltar($cargo,$str)."</td>
                    <td>".resaltar($apellidos,$str1)."</td>
                    <td>".resaltar($nombres,$str2)."</td>
                    <td align='center'>
                      <form name='formModif".$fila["empleadoID"]."' method='post' action='empleado_modificar1.php'>
                      <input type='hidden' name='empleadoID' value='".$fila['empleadoID']."'>
                      <a href='javascript:document.formModif".$fila['empleadoID'].".submit();' title='Modificar Opcion Sistema'>
                              Modificar>>
                            </a>
                          </form>
                        </td>
                        <td align='center'>  
                          <form name='formElimi".$fila["empleadoID"]."' method='post' action='empleado_eliminar.php'>
                            <input type='hidden' name='empleadoID' value='".$fila["empleadoID"]."'>
                            <a href='javascript:document.formElimi".$fila['empleadoID'].".submit();' title='Eliminar Opcion Sistema' onclick='javascript:return(confirm(\"Desea realmente Eliminar la opción ".$fila["cargo"]." ?\"))'; location.href='empleado_eliminar.php''> 
                              Eliminar>>
                            </a>
                          </form>                        
                        </td>
                </tr>";
            }
             echo"</table>
          </center>";
    }else{
      echo"<center><b> LA OPCIÓN NO EXISTE!!</b></center><br>";
    }
  }
 ?>