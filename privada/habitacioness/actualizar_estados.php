<?php
require_once("../../conexion.php");

// Consulta SQL para obtener los estados de las habitaciones
$sql = $db->Prepare("SELECT h.habitacionID, h.numero, h.estado 
                     FROM habitaciones h 
                     WHERE h._estado <> 'X'
                     ORDER BY h.numero ASC;");
$rs = $db->GetAll($sql);

// Devolver los resultados como JSON
echo json_encode($rs);
?>
