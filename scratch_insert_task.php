<?php
require 'conexion.php';

$tareas = [
    ['agregar FK en hospedajes_auditoria_montos hacia hospedajes', 'CORTO'],
    ['revisar cual sera la fk de recaudaciones', 'CORTO'],
    ['tabla entre ingresos y egresos o solo indices para optimizar consultas', 'CORTO'],
    ['implementacion de doble partida contable', 'LARGO'],
];

foreach ($tareas as $t) {
    $db->ejecutar(
        "INSERT INTO planes (tarea, tipo, estado) VALUES (?, ?, 'PENDIENTE')",
        [$t[0], $t[1]]
    );
    echo "Insertado: [{$t[1]}] {$t[0]}\n";
}
