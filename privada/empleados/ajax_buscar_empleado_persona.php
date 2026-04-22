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
if ($paterno or $materno or $nombres or $ci or $fecha) {
    $sql3 = $db->Prepare("SELECT *
                          FROM EMPLEADOS
                          WHERE ap LIKE ?
                          AND am LIKE ?
                          AND nombres LIKE ?
                          AND ci LIKE ?
                          AND _fec_insercion LIKE ?
                          AND _estado <> 'X'");
    $rs3 = $db->GetAll($sql3, array($paterno."%", $materno."%", $nombres."%", $ci."%", $fecha."%"));
    
    if ($rs3) {
        echo "<div class='table-responsive'>
              <table class='table table-striped'>
              <thead>
                <tr>
                    <th scope='col'>C.I.</th>
                    <th scope='col'>Paterno</th>
                    <th scope='col'>Materno</th>
                    <th scope='col'>Nombres</th>
                    <th scope='col'>Fecha</th>
                    <th scope='col'><img src='../../imagenes/modificar.gif'></th>
                    <th scope='col'><img src='../../imagenes/borrar.jpeg'></th>
                </tr>
              </thead>
              <tbody>";
        
        foreach ($rs3 as $k => $fila) {
            $str = $fila["ci"];
            $str1 = $fila["ap"];
            $str2 = $fila["am"];
            $str3 = $fila["nombres"];
            $str4 = $fila["_fec_insercion"];

            echo "<tr>
                    <td>".resaltar($ci, $str)."</td>
                    <td>".resaltar($paterno, $str1)."</td>
                    <td>".resaltar($materno, $str2)."</td>
                    <td>".resaltar($nombres, $str3)."</td>
                    <td>".resaltar($fecha, $str4)."</td>
                    <td>
                        <form name='formModif".$fila["id_Empleado"]."' method='post' action='Empleado_modificar.php' style='display:inline;'>
                            <input type='hidden' name='id_Empleado' value='".$fila['id_Empleado']."'>
                            <button type='submit' class='btn btn-sm btn-primary btn-accion'>Modificar</button>
                        </form>
                    </td>
                    <td>
                        <form name='formElimi".$fila["id_Empleado"]."' method='post' action='Empleado_eliminar.php' style='display:inline;'>
                            <input type='hidden' name='id_Empleado' value='".$fila["id_Empleado"]."'>
                            <button type='submit' class='btn btn-sm btn-danger btn-accion' onclick='return confirm(\"Desea realmente eliminar a la Empleado ".$fila["nombres"]." ".$fila["ap"]." ".$fila["am"]." ?\");'>Eliminar</button>
                        </form>
                    </td>
                  </tr>";
        }
        
        echo "</tbody>
              </table>
              </div>";
    } else {
        echo "<center><b>LA Empleado NO EXISTE!!</b></center><br>";
    }
}
?>
