<?php
require_once("../../conexion.php");

$nombre = isset($_POST['nombre']) ? "%" . $_POST['nombre'] . "%" : "%%";
$apellido = isset($_POST['apellido']) ? "%" . $_POST['apellido'] . "%" : "%%";
$ci = isset($_POST['ci']) ? "%" . $_POST['ci'] . "%" : "%%";

// Nueva consulta SQL para buscar solo en la tabla de clientes
$sql = $db->Prepare("   SELECT  c.nombres, c.apellidos, c.ci, c.fecha_nacimiento, c.lugar_nacimiento,clienteID
                        FROM    clientes c
                        WHERE   c._estado <> 'X'
                        AND     (c.nombres LIKE ? OR c.apellidos LIKE ? OR c.ci LIKE ?)
                        ORDER BY c.clienteID DESC
");

$rs = $db->GetAll($sql, array($nombre, $apellido, $ci));

header("Content-Type: application/json");
echo json_encode($rs);
?>