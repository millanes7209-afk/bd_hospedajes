<?php
require_once("../../conexion.php");

$ci = $_GET['ci'];
$sql = "SELECT CONCAT_WS(' ',nombres, apellidos) as cliente, ci FROM clientes WHERE ci LIKE ? OR nombres LIKE ?";
$params = ["%$ci%", "%$ci%"];
$rs = $db->GetAll($sql, $params);

echo json_encode($rs);
?>
