<?php
session_start();
require_once("../../conexion.php");
require_once("../../paginacion.inc.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
         <meta http-equiv='Content-Type' content='text/html;charset=utf-8' />
       </head>
       <body>
       <p> &nbsp;</p>";
       
       contarRegistros($db,"EMPLEADOS");

paginacion("EMPLEADOS.php?");        

$sql = $db->Prepare("SELECT     *
                     FROM       EMPLEADOS
                     WHERE      _estado <> 'X' 
                     AND        id_Empleado > 1
                     ORDER BY   id_Empleado ASC                    
                     LIMIT      ? OFFSET ?
                        ") ;
$rs = $db->GetAll($sql,array($nElem,$regIni));
   if ($rs) {
        echo"<center>
              <h1>LISTADO DE EMPLEADOS</h1>
              <b><a  href='Empleado_nuevo.php'>Nueva Empleado>>>></a></b>
              <table class='listado'>
                <tr>                                    
                  <th>Nro</th><th>C.I.</th><th>PATERNO</th><th>MATERNO</th><th>NOMBRES</th>
                  <th><img src='../../imagenes/modificar.gif'></th><th><img src='../../imagenes/borrar.jpeg'></th>
                </tr>";
                $b=0;
                $total=$pag-1;
                $a=$nElem*$total;
                $b=$b+1+$a;
            foreach ($rs as $k => $fila) {                                       
                echo"<tr>
                        <td align='center'>".$b."</td>
                        <td align='center'>".$fila['ci']."</td>
                        <td>".$fila['ap']."</td>
                        <td align='center'>".$fila['am']."</td>
                        <td>".$fila['nombres']."</td>
                        <td align='center'>
                          <form name='formModif".$fila["id_Empleado"]."' method='post' action='Empleado_modificar.php'>
                            <input type='hidden' name='id_Empleado' value='".$fila['id_Empleado']."'>
                            <a href='javascript:document.formModif".$fila['id_Empleado'].".submit();' title='Modificar Empleado Sistema'>
                              Modificar>>
                            </a>
                          </form>
                        </td>
                        <td align='center'>  
                          <form name='formElimi".$fila["id_Empleado"]."' method='post' action='Empleado_eliminar.php'>
                            <input type='hidden' name='id_Empleado' value='".$fila["id_Empleado"]."'>
                            <a href='javascript:document.formElimi".$fila['id_Empleado'].".submit();' title='Eliminar Empleado Sistema' onclick='javascript:return(confirm(\"Desea realmente Eliminar a la Empleado ".$fila["nombres"]." ".$fila["ap"]." ".$fila["am"]." ?\"))'; location.href='Empleado_eliminar.php''> 
                              Eliminar>>
                            </a>
                          </form>                        
                        </td>
                     </tr>";
                     $b=$b+1;
            }
             echo"</table>
          </center>";
    }
    mostrar_paginacion();
    
echo "</body>
      </html> ";

 ?>
