<?php
require_once('conexion.php');

try {
    // 1. Obtener el ID del grupo RECEPCION (Sabemos que es 1, pero lo buscamos por seguridad)
    $res = $db->obtenerFila("SELECT grupoID FROM grupos WHERE grupo = 'RECEPCION' AND _estado <> 'X'");
    $grupoID = $res['grupoID'];

    // 2. Definir las nuevas opciones
    $opciones = [
        ['opcion' => 'TIPO HABITACIONES', 'contenido' => 'habitacioness/tipos.php', 'orden' => 60],
        ['opcion' => 'LISTADO HABITACIONES', 'contenido' => 'habitacioness/habit_lista.php', 'orden' => 70]
    ];

    foreach ($opciones as $op) {
        $path = "../privada/" . $op['contenido'];

        // Verificar si ya existe para no duplicar
        $check = $db->obtenerFila("SELECT opcionID FROM opciones WHERE contenido = ?", [$path]);

        if (!$check) {
            $ahora = date('Y-m-d H:i:s');
            $sql_op = "INSERT INTO opciones (grupoID, opcion, contenido, orden, _fec_insercion, _usuario, _estado) 
                       VALUES (?, ?, ?, ?, ?, 1, 'A')";
            $db->ejecutar($sql_op, [$grupoID, $op['opcion'], $path, $op['orden'], $ahora]);
            $opcionID = $db->lastInsertId();
            echo "Opción '{$op['opcion']}' creada (ID: $opcionID).\n";

            // DAR ACCESO AL PROPIETARIO (Rol 3) mentalmente solicitado por el usuario
            // El Admin ya tiene acceso por el TRIGGER.
            $sql_acc = "INSERT INTO accesos (rolID, opcionID, _fec_insercion, _usuario, _estado) 
                        VALUES (3, ?, ?, 1, 'A')";
            $db->ejecutar($sql_acc, [$opcionID, $ahora]);
            echo "Acceso concedido al PROPIETARIO para '{$op['opcion']}'.\n";
        } else {
            echo "La opción '{$op['opcion']}' ya existe.\n";
        }
    }

    echo "ACTIVACION_COMPLETA: Las pestañas de administración de habitaciones ya son visibles para Admin y Propietario.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>