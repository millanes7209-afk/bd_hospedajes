<?php
session_start();
require_once("../../conexion.php");
require_once("../../paginacion.inc.php");
require_once("../../libreria_menu.php");
//$db->debug=true;

echo"<html> 
       <head>
         <link rel='stylesheet' href='../../css/estilos.css' type='text/css'>
         <meta http-equiv='Content-type' content='text/html;charset=utf-8' />
         <script type='text/javascript' src='../../ajax.js'></script>
         <script type='text/javascript' src='js/buscar_EMPLEADOS.js'></script>
       </head>
       <body>
       <p> &nbsp;</p>";


       echo"
<!-------INICIO BUSCADOR ------------>
    <center>
    <h1 align='center'>LISTADO DE EMPLEADOS</h1>
    <b><a href='Empleado_nuevo.php'>Nueva Empleado>>></a></b>
        <form action='#'' method='post' name='formu'>
            <table border='1' class='listado'>
                <tr>
                    <th>
                        <b>Paterno</b><br />
                        <input type='text' name='paterno' value='' size='10' onKeyUp='buscar_EMPLEADOS()'>
                    </th>
                    <th>
                        <b>Materno</b><br />
                        <input type='text' name='materno' value='' size='10' onKeyUp='buscar_EMPLEADOS()'>
                    </th>
                    <th>
                        <b>Nombres</b><br />
                        <input type='text' name='nombres' value='' size='10' onKeyUp='buscar_EMPLEADOS()'>
                    </th>
                    <th>
                        <b>C.I.</b><br />
                        <input type='text' name='ci' value='' size='10' onKeyUp='buscar_EMPLEADOS()'>
                    </th>
                    <th>
                        <b>Fecha Insercion</b><br />
                        <input type='date' name='fecha' value='' size='10' onchange='buscar_EMPLEADOS()'>
                    </th>
                </tr>
            </table>
        </form>
    </center>
<!--FIN BUSCADOR --------------->
";
       

echo "<div id='EMPLEADOS1'>";       
contarRegistros($db,"empleados");

paginacion("EMPLEADOS.php?");        

$sql = $db->Prepare("SELECT     *
                     FROM       empleados
                     WHERE      _estado <> 'X' 
                     AND        empleadoID > 1
                     ORDER BY   empleadoID DESC                    
                     LIMIT      ? OFFSET ?
                        ") ;
$rs = $db->GetAll($sql,array($nElem,$regIni));
   if ($rs) {
        echo"<center>
              
              <table class='listado'>
                <tr>                                    
                  <th>Nro</th><th>C.I.</th><th>PATERNO</th><th>MATERNO</th><th>NOMBRES</th>
                  <th><img src='../../imagenes/modificar.gif'></th><th><img src='../../imagenes/borrar.jpeg'  ></th>
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
                        <td>".$fila['am']."</td>
                        <td>".$fila['nombres']."</td>
                        <td align='center'>
                          <form name='formModif".$fila["empleadoID"]."' method='post' action='Empleado_modificar.php'>
                            <input type='hidden' name='empleadoID' value='".$fila['empleadoID']."'>
                            <a href='javascript:document.formModif".$fila['empleadoID'].".submit();' title='Modificar Empleado Sistema'>
                              Modificar>>
                            </a>
                          </form>
                        </td>
                        <td align='center'>  
                          <form name='formElimi".$fila["empleadoID"]."' method='post' action='Empleado_eliminar.php'>
                            <input type='hidden' name='empleadoID' value='".$fila["empleadoID"]."'>
                            <a href='javascript:document.formElimi".$fila['empleadoID'].".submit();' title='Eliminar Empleado Sistema' 
                            onclick='javascript:return(confirm(\"Desea realmente Eliminar a la Empleado ".$fila["nombres"]." ".$fila["ap"]." ".$fila["am"]." ?\"))'; location.href='Empleado_eliminar.php''> 
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
  echo"</div>";
echo "</body>
      </html> ";

 ?>
