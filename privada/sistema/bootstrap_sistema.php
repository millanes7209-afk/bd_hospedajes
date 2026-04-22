<?php
require_once('conexion.php');

try {
    // 1. Crear el grupo SISTEMA
    $sql_grupo = "INSERT INTO grupos (grupo, _fec_insercion, _usuario, _estado) VALUES ('SISTEMA', NOW(), 1, 'A')";
    $db->ejecutar($sql_grupo);
    $grupoID = $db->lastInsertId();
    echo "Grupo SISTEMA creado (ID: $grupoID)\n";

    // 2. Crear las opciones (El Trigger les dará acceso al Admin automaticamente)
    $opciones = [
        ['opcion' => 'GESTION GRUPOS', 'contenido' => 'sistema/grupos.php', 'orden' => 10],
        ['opcion' => 'GESTION OPCIONES', 'contenido' => 'sistema/opciones.php', 'orden' => 20],
        ['opcion' => 'GESTION PERMISOS', 'contenido' => 'sistema/accesos.php', 'orden' => 30]
    ];

    foreach ($opciones as $op) {
        $sql_op = "INSERT INTO opciones (grupoID, opcion, contenido, orden, _fec_insercion, _usuario, _estado) 
                   VALUES (?, ?, ?, ?, NOW(), 1, 'A')";
        $db->ejecutar($sql_op, [$grupoID, $op['opcion'], $op['contenido'], $op['orden']]);
        echo "Opción '{$op['opcion']}' creada.\n";
    }

    echo "BOOTSTRAP_COMPLETO: El módulo Sistema está listo para usarse.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
