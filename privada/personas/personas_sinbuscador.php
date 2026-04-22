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
       
       contarRegistros($db,"personas");

paginacion("personas.php?");        

$sql = $db->Prepare("SELECT     *
                     FROM       personas
                     WHERE      _estado <> 'X' 
                     AND        id_persona > 1
                     ORDER BY   id_persona ASC                    
                     LIMIT      ? OFFSET ?
                        ") ;
$rs = $db->GetAll($sql,array($nElem,$regIni));
   if ($rs) {
        echo"<center>
              <h1>LISTADO DE PERSONAS</h1>
              <b><a  href='persona_nuevo.php'>Nueva Persona>>>></a></b>
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
                            <a href='javascript:document.formElimi".$fila['id_persona'].".submit();' title='Eliminar Persona Sistema' onclick='javascript:return(confirm(\"Desea realmente Eliminar a la persona ".$fila["nombres"]." ".$fila["ap"]." ".$fila["am"]." ?\"))'; location.href='persona_eliminar.php''> 
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