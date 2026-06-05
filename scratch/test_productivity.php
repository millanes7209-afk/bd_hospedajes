<?php
require_once 'c:/xampp/htdocs/dulces/sis_segundo_2023/conexion.php';

echo "--- INICIANDO REASIGNACIÓN ALEATORIA AL USUARIO 4 ---\n";

try {
    // 1. Reasignar INGRESOS
    // Buscamos ingresos que NO sean del usuario 4 y seleccionamos 10 al azar
    $ingresos = $db->obtenerTodo("SELECT ingresoID FROM ingresos WHERE _usuario <> 4 AND _estado <> 'X' ORDER BY RAND() LIMIT 10");

    if (count($ingresos) > 0) {
        $ids = array_column($ingresos, 'ingresoID');
        $idList = implode(',', $ids);

        $db->ejecutar("UPDATE ingresos SET _usuario = 4, usuarioID = 4 WHERE ingresoID IN ($idList)");
        echo "Se han reasignado " . count($ingresos) . " ingresos al Usuario 4.\n";
    } else {
        echo "No se encontraron ingresos para reasignar.\n";
    }

    // 2. Reasignar EGRESOS
    // Buscamos egresos que NO sean del usuario 4 y seleccionamos 5 al azar
    $egresos = $db->obtenerTodo("SELECT egresoID FROM egresos WHERE _usuario <> 4 AND _estado <> 'X' ORDER BY RAND() LIMIT 5");

    if (count($egresos) > 0) {
        $idsE = array_column($egresos, 'egresoID');
        $idListE = implode(',', $idsE);

        $db->ejecutar("UPDATE egresos SET _usuario = 4, usuarioID = 4 WHERE egresoID IN ($idListE)");
        echo "Se han reasignado " . count($egresos) . " egresos al Usuario 4.\n";
    } else {
        echo "No se encontraron egresos para reasignar.\n";
    }

    echo "\n--- PROCESO COMPLETADO --- \nRecarga tu pantalla de estadísticas para ver los cambios.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>