<?php
/**
 * SNAPSHOT: ULALA
 * Punto de referencia previo a la reestructuración contable.
 * 
 * Fecha: <?php echo date('Y-m-d H:i:s'); ?>
 * 
 * Descripción:
 * Este script registra el estado actual de la base de datos como punto
 * de inicio antes de la migración de movimientos+gastos → ingresos+egresos+cuentas.
 */

require 'conexion.php';

$ahora = date('Y-m-d H:i:s');
$migracion = 'ULALA';

// Obtener todas las tablas actuales con su conteo de registros
$tablas = $db->obtenerTodo("
    SELECT TABLE_NAME, TABLE_ROWS
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = 'bd_hospedajes'
    ORDER BY TABLE_NAME
");

$snapshots = [];
foreach ($tablas as $t) {
    $nombre = $t['TABLE_NAME'];
    // Contar registros reales (TABLE_ROWS en InnoDB es aproximado)
    $count = $db->obtenerFila("SELECT COUNT(*) AS total FROM `$nombre`");
    $snapshots[] = [
        'tabla'    => $nombre,
        'registros' => $count['total'],
    ];
}

// Insertar en migracion_log una fila por tabla
$db->beginTransaction();
try {
    foreach ($snapshots as $s) {
        $db->ejecutar(
            "INSERT INTO migracion_log 
             (tabla_origen, tabla_destino, registros_origen, registros_migrados, fecha_migracion, estado, error_detalle, usuario_migracion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $s['tabla'],
                'SNAPSHOT_ULALA',
                $s['registros'],
                0,
                $ahora,
                'COMPLETADO',
                'Snapshot pre-migración: punto de inicio ULALA',
                1
            ]
        );
    }
    $db->commit();
    echo "✅ Snapshot ULALA registrado correctamente.\n";
    echo "   Tablas capturadas: " . count($snapshots) . "\n\n";
    foreach ($snapshots as $s) {
        echo "   - {$s['tabla']}: {$s['registros']} registros\n";
    }
} catch (Exception $e) {
    $db->rollBack();
    echo "❌ Error: " . $e->getMessage();
}
