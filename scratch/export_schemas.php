<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

function exportSchema($db, $schemaName, $fileName)
{
    echo "Exportando estructura de $schemaName a $fileName...\n";
    $outputFile = "c:/xampp/htdocs/dulces/sis_segundo_2023/privada/" . $fileName;
    $sqlContent = "-- Estructura de la base de datos: $schemaName\n";
    $sqlContent .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlContent .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tablas = $db->obtenerTodo("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$schemaName' AND TABLE_TYPE = 'BASE TABLE'");

    foreach ($tablas as $t) {
        $tableName = $t['TABLE_NAME'];
        $res = $db->obtenerTodo("SHOW CREATE TABLE `$schemaName`.`$tableName`");
        if ($res && count($res) > 0) {
            $createTableSql = array_values($res[0])[1];
            $sqlContent .= "-- Estructura de tabla para `$tableName` --\n";
            $sqlContent .= $createTableSql . ";\n\n";
        }
    }

    $sqlContent .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    file_put_contents($outputFile, $sqlContent);
    echo "Hecho.\n";
}

// Generar ambos archivos
exportSchema($db, 'bd_hospedajes', 'hospedajes.sql');
exportSchema($db, 'bd_dulces', 'dulces.sql');
?>