<?php
require 'conexion.php';

try {
    // 1. Obtener 5 IDs aleatorios de ingresos del usuario 4 antes de Mayo 2026
    $sql = "SELECT ingresoID FROM ingresos 
            WHERE usuarioID = 4 
            AND fecha < '2026-05-01' 
            AND _estado <> 'X'
            ORDER BY RAND() 
            LIMIT 5";

    $ingresos = $db->obtenerTodo($sql);

    if (empty($ingresos)) {
        echo "No se encontraron ingresos para el usuario 4 en ese rango de fechas.\n";
        exit();
    }

    echo "PROCESANDO CAMBIOS ALEATORIOS...\n";
    foreach ($ingresos as $i) {
        $id = $i['ingresoID'];
        // Elegir aleatoriamente entre 7 y 9
        $nuevoUsuario = (rand(0, 1) == 0) ? 7 : 9;

        echo " - Ingreso ID: $id -> Reasignado a Usuario: $nuevoUsuario\n";

        // Actualizar tabla INGRESOS
        $db->ejecutar("UPDATE ingresos SET usuarioID = ?, _usuario = ? WHERE ingresoID = ?", [$nuevoUsuario, $nuevoUsuario, $id]);

        // Actualizar tabla INGRESO_PAGOS
        $db->ejecutar("UPDATE ingreso_pagos SET _usuario = ? WHERE ingresoID = ?", [$nuevoUsuario, $id]);
    }

    echo "\n¡Cambio completado exitosamente para 5 registros!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
