<?php
require_once("c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php");

// 1. Análisis de Lugar de Nacimiento (Top 50)
echo "--- ANÁLISIS DE LUGAR DE NACIMIENTO (Frecuencias) ---\n";
$sql_lugar = "SELECT lugar_nacimiento, COUNT(*) as total 
              FROM bd_dulces.clientes 
              GROUP BY lugar_nacimiento 
              ORDER BY total DESC 
              LIMIT 50";
$lugares = $db->obtenerTodo($sql_lugar);
foreach ($lugares as $l) {
    echo "{$l['total']}\t: {$l['lugar_nacimiento']}\n";
}

echo "\n--- ANÁLISIS DE PATRONES DE CI ---\n";

// Patrón Argentina (Empieza con 9, tiene 8 números)
$sql_arg = "SELECT COUNT(*) as total FROM bd_dulces.clientes WHERE ci REGEXP '^9[0-9]{7}$'";
$res_arg = $db->obtenerFila($sql_arg);
echo "Posible Argentina (Empieza con 9, 8 dígitos): " . $res_arg['total'] . "\n";

// Patrón Bolivia común (7 dígitos)
$sql_bol7 = "SELECT COUNT(*) as total FROM bd_dulces.clientes WHERE ci REGEXP '^[0-9]{7}$'";
$res_bol7 = $db->obtenerFila($sql_bol7);
echo "Posible Bolivia (7 dígitos): " . $res_bol7['total'] . "\n";

// Patrón Bolivia nuevo/extenso (8 dígitos, empieza con 1)
$sql_bol8 = "SELECT COUNT(*) as total FROM bd_dulces.clientes WHERE ci REGEXP '^1[0-9]{7}$'";
$res_bol8 = $db->obtenerFila($sql_bol8);
echo "Posible Bolivia (8 dígitos, empieza con 1): " . $res_bol8['total'] . "\n";

// Otros (No encajan en los patrones anteriores)
$sql_otros = "SELECT COUNT(*) as total FROM bd_dulces.clientes 
              WHERE ci NOT REGEXP '^9[0-9]{7}$' 
              AND ci NOT REGEXP '^[0-9]{7}$' 
              AND ci NOT REGEXP '^1[0-9]{7}$'";
$res_otros = $db->obtenerFila($sql_otros);
echo "Desconocidos/Otros CI: " . $res_otros['total'] . "\n";

// Ejemplos de CIs "extraños"
echo "\n--- EJEMPLOS DE CIs QUE NO ENCAJAN ---\n";
$sql_ejemplos = "SELECT ci, nombres, apellidos, lugar_nacimiento FROM bd_dulces.clientes 
                 WHERE ci NOT REGEXP '^[0-9]+$' 
                 OR (LENGTH(ci) < 6 OR LENGTH(ci) > 10) 
                 LIMIT 10";
$ejemplos = $db->obtenerTodo($sql_ejemplos);
foreach ($ejemplos as $e) {
    echo "CI: {$e['ci']} | {$e['nombres']} {$e['apellidos']} | Lugar: {$e['lugar_nacimiento']}\n";
}
?>