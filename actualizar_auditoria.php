<?php
require_once("conexion.php");

try {
    // 1. Actualizar Vista v_movimientos_caja
    $sqlView = "CREATE OR REPLACE VIEW v_movimientos_caja AS
        SELECT 
            'INGRESO'     AS tipo,
            i.ingresoID   AS movimientoID,
            i.cajaID,
            i.empresaID,
            i.usuarioID,
            i.cuentaID,
            c.codigo      AS cuenta_codigo,
            c.nombre      AS cuenta_nombre,
            i.monto_total AS monto,
            i.concepto,
            i.fecha,
            i._estado,
            i.recaudacionID,
            i.forma_pago,
            i._fec_insercion
        FROM ingresos i
        INNER JOIN cuentas c ON i.cuentaID = c.cuentaID
        WHERE i._estado <> 'X'

        UNION ALL

        SELECT 
            'EGRESO'      AS tipo,
            e.egresoID    AS movimientoID,
            e.cajaID,
            e.empresaID,
            e.usuarioID,
            e.cuentaID,
            c.codigo      AS cuenta_codigo,
            c.nombre      AS cuenta_nombre,
            e.monto_total AS monto,
            e.concepto,
            e.fecha,
            e._estado,
            NULL          AS recaudacionID,
            e.forma_pago,
            e._fec_insercion
        FROM egresos e
        INNER JOIN cuentas c ON e.cuentaID = c.cuentaID
        WHERE e._estado <> 'X'";
    
    $db->ejecutar($sqlView);
    echo "✅ Vista v_movimientos_caja actualizada con usuarioID.\n";

    // 2. Crear Índices de Rendimiento
    // (Usamos try-catch interno porque ALTER TABLE no soporta IF NOT EXISTS en todos los motores antiguos)
    try { $db->ejecutar("ALTER TABLE ingresos ADD INDEX idx_caja_empresa (cajaID, empresaID)"); } catch(Exception $e){}
    try { $db->ejecutar("ALTER TABLE egresos ADD INDEX idx_caja_empresa (cajaID, empresaID)"); } catch(Exception $e){}
    try { $db->ejecutar("ALTER TABLE ingresos ADD INDEX idx_fecha (fecha)"); } catch(Exception $e){}
    try { $db->ejecutar("ALTER TABLE egresos ADD INDEX idx_fecha (fecha)"); } catch(Exception $e){}
    
    echo "✅ Índices de rendimiento creados (o ya existían).";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
