<?php
require_once "c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php";
$empresaID = 1; // Testing with company 1
$sql = "SELECT count(*) as total, sum(case when cajaID is null then 1 else 0 end) as sin_caja FROM recaudaciones WHERE empresaID = ?";
$rs = $db->obtenerFila($sql, [$empresaID]);
print_r($rs);
