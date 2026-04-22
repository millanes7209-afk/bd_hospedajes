<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
       </head>
       <body>
       <p> &nbsp;</p>";
       
        

$sql = $db->Prepare("SELECT     *
                     FROM       emp_mensajeria
                     WHERE      _estado <> 'X' 
                     ORDER BY   empresaID DESC                      
                        ");
$rs = $db->GetAll($sql);
   if ($rs) {
        echo"<center>
              <h1>LISTADO DE PERSONAS</h1>
              
              <table class='listado'>
                <tr>                                    
                  <th>Nro</th><th>EMPRESA</th><th>DIRECCION</th><th>TELEFONO</th>
                  <th><img src='../../imagenes/modificar.gif'></th><th><img src='../../imagenes/borrar.jpeg'></th>
                </tr>";
                $b=1;
            foreach ($rs as $k => $fila) {                                       
                echo"<tr>
                        <td align='center'>".$b."</td>
                        <td align='center'>".$fila['nombre']."</td>
                        <td>".$fila['direccion']."</td>
                        <td align='center'>".$fila['telefono']."</td>
                        <td align='center'>
                          <form name='formModif".$fila["empresaID"]."' method='post' action='unibol_modificar.php'>
                            <input type='hidden' name='empresaID' value='".$fila['empresaID']."'>
                            <a href='javascript:document.formModif".$fila['empresaID'].".submit();' title='Modificar Empresa Sistema'>
                              Modificar>>
                            </a>
                          </form>
                        </td>
                        <td align='center'>  
                          <form name='formElimi".$fila["empresaID"]."' method='post' action='persona_eliminar.php'>
                            <input type='hidden' name='empresaID' value='".$fila["empresaID"]."'>
                            <a href='javascript:document.formElimi".$fila['empresaID'].".submit();' title='Eliminar Persona Sistema' onclick='javascript:return(confirm(\"Desea realmente Eliminar a la persona ".$fila["nombre"]." ".$fila["direccion"]." ".$fila["telefono"]." ?\"))'; location.href='persona_eliminar.php''> 
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

echo "</body>
      </html> ";

 ?>