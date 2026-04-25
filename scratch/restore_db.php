<?php
require_once(__DIR__ . '/../conexion.php');

try {
    // 1. Eliminar accesos de las nuevas opciones
    $resAud = $db->obtenerTodo("SELECT opcionID FROM opciones WHERE contenido = 'hospedajes/hospedajes_auditoria.php'");
    $resRec = $db->obtenerTodo("SELECT opcionID FROM opciones WHERE contenido = 'movimientos/vista_cajas.php'");
    
    if ($resAud) {
        $db->ejecutar("DELETE FROM accesos WHERE opcionID = ?", [$resAud[0]['opcionID']]);
    }
    
    // 2. Eliminar opciones (Solo la de Auditoría, la de Vista Cajas ya existía pero quizás agregué una duplicada?)
    // Realmente agregué una nueva en el grupo 3 y 7. 
    // Como usé INSERT IGNORE, voy a limpiar específicamente las que creé por error.
    
    $db->ejecutar("DELETE FROM opciones WHERE contenido = 'hospedajes/hospedajes_auditoria.php'");
    
    // Nota: 'movimientos/vista_cajas.php' ya existía originalmente, pero yo inserté una nueva con nombre 'CONTROL DE RECAUDACIONES'.
    $db->ejecutar("DELETE FROM opciones WHERE opcion = 'CONTROL DE RECAUDACIONES'");

    // 3. Eliminar grupo AUDITORÍA
    $db->ejecutar("DELETE FROM grupos WHERE grupo = 'AUDITORÍA'");

    echo "Base de datos restaurada correctamente.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
