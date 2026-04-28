<?php
/**
 * FASE 4 - ULALA Migration
 * Crear vista v_movimientos_caja para reportes unificados de ingresos y egresos.
 */
require 'conexion.php';

// Eliminar si existe una versión previa
$db->ejecutar("DROP VIEW IF EXISTS v_movimientos_caja");

$sql = "CREATE VIEW v_movimientos_caja AS
    SELECT 
        'INGRESO'     AS tipo,
        i.ingresoID   AS id,
        i.cajaID,
        i.empresaID,
        i.cuentaID,
        c.codigo      AS cuenta_codigo,
        c.nombre      AS cuenta_nombre,
        i.monto_total,
        i.concepto,
        i.fecha,
        i._estado
    FROM ingresos i
    JOIN cuentas c ON i.cuentaID = c.cuentaID

    UNION ALL

    SELECT 
        'EGRESO'      AS tipo,
        e.egresoID    AS id,
        e.cajaID,
        e.empresaID,
        e.cuentaID,
        c.codigo      AS cuenta_codigo,
        c.nombre      AS cuenta_nombre,
        e.monto_total,
        e.concepto,
        e.fecha,
        e._estado
    FROM egresos e
    JOIN cuentas c ON e.cuentaID = c.cuentaID";

$resultado = $db->ejecutar($sql);
if ($resultado !== false) {
    echo "✅ Vista 'v_movimientos_caja' creada correctamente.\n";
    echo "\nColumnas disponibles:\n";
    $cols = $db->obtenerTodo("DESCRIBE v_movimientos_caja");
    foreach ($cols as $col) {
        echo "   - {$col['Field']}\n";
    }
} else {
    echo "❌ Error al crear la vista.\n";
}
