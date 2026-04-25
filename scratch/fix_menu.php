<?php
require_once(__DIR__ . '/../conexion.php');

try {
    // 1. Limpiar duplicados de vista_cajas.php
    // Primero borramos todas las que apunten a ese contenido
    $db->ejecutar("DELETE FROM accesos WHERE opcionID IN (SELECT opcionID FROM opciones WHERE contenido = 'movimientos/vista_cajas.php')");
    $db->ejecutar("DELETE FROM opciones WHERE contenido = 'movimientos/vista_cajas.php'");
    
    // 2. Insertar UNA SOLA vez en MOVIMIENTOS (ID 3)
    $db->ejecutar("INSERT INTO opciones (grupoID, opcion, contenido, orden, _usuario, _estado) 
                   VALUES (3, 'REPORTE DE CAJAS (PENDIENTES)', 'movimientos/vista_cajas.php', 1, 1, 'A')");
    $newID = $db->ultimoInsertId();
    
    // 3. Asignar accesos a todos los roles
    foreach ([1, 2, 3] as $rolID) {
        $db->ejecutar("INSERT INTO accesos (opcionID, rolID, _usuario, _estado) VALUES (?, ?, 1, 'A')", [$newID, $rolID]);
    }

    echo "Menú simplificado con una sola opción de Reporte de Cajas.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
