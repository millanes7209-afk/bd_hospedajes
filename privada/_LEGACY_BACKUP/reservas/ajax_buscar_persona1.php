<?php
session_start();
require_once("../../conexion.php");
require_once("../../resaltarBusqueda.inc.php");

$clienteID = $_POST["clienteID"];
// Consultar datos de la persona
$sql3 = $db->Prepare("SELECT ci, CONCAT_WS(' ', apellidos,nombres)as cliente
                      FROM clientes
                      WHERE clienteID = ?
                      AND _estado <> 'X'");
$rs3 = $db->GetAll($sql3, array($clienteID));

echo "
<div class='col-md-8'>
    <div class='table-responsive'>
        <table class='table table-bordered'>
            <thead>
                <tr>
                    <th colspan='4' class='text-center'>Cliente</th>
                </tr>
                <tr>
                    <th scope='col'>C.I.</th>
                    <th scope='col'>Cliente</th>                    
                </tr>
            </thead>
            <tbody>";

foreach ($rs3 as $k => $fila) {
    echo "<tr>
            <td align='center'>" . $fila['ci'] . "</td>
            <td>" . $fila['cliente'] . "</td>
          </tr>";
}

echo "</tbody>
      </table>
      </div>";


/* Consultar datos de los usuarios asociados a la persona
$sql4 = $db->Prepare("SELECT *
                      FROM incidentes
                      WHERE clienteID = ?
                      AND _estado <> 'X'");
$rs4 = $db->GetAll($sql4, array($clienteID));

echo "<div class='table-responsive'>
        <table class='table table-bordered'>
            <thead>
                <tr>
                    <th colspan='4' class='text-center'>Datos Incidentes</th>
                </tr>
                <tr>
                    <th scope='col'>Incidente</th>
                    <th scope='col'>Fecha</th>
                </tr>
            </thead>
            <tbody>";

if ($rs4) {
    foreach ($rs4 as $k => $fila) {
        echo "<tr>
                <td align='center'>" . $fila["estado"] . "</td>
                <td align='center'>" . $fila["fecha_reserva"] . "</td>
              </tr>";
    }
} else {
    echo "<tr>
            <td align='center'>NO REGISTRA INCIDENTES</td>
          </tr>";
}
*/
echo "</tbody>
      </table>
      </div>
      </div>";
?>
