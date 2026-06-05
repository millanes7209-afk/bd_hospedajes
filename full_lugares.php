<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");

echo "--- LISTADO COMPLETO DE LUGARES DE NACIMIENTO (Frecuencias) ---\n";
// Getting more than just top 50 to catch more errors
$sql_lugar = "SELECT lugar_nacimiento, COUNT(*) as total 
              FROM bd_dulces.clientes 
              GROUP BY lugar_nacimiento 
              ORDER BY total DESC";
$lugares = $db->obtenerTodo($sql_lugar);
foreach ($lugares as $l) {
    echo "{$l['total']}\t: {$l['lugar_nacimiento']}\n";
}
?>