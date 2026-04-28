<?php
require_once("../conexion.php");

$sql = "CREATE OR REPLACE VIEW v_movimientos_caja AS
SELECT 
    'INGRESO' as tipo, 
    i.ingresoID as movimientoID, 
    i.cajaID, 
    i.empresaID, 
    i.cuentaID, 
    c.codigo as cuenta_codigo, 
    c.nombre as cuenta_nombre, 
    ip.monto, 
    i.concepto, 
    i.fecha, 
    i._estado, 
    ip.formapagoID, 
    fp.tipo as forma_pago, 
    i.entregado, 
    i.recaudacionID, 
    i._usuario as usuarioID
FROM ingresos i
JOIN ingreso_pagos ip ON i.ingresoID = ip.ingresoID
JOIN cuentas c ON i.cuentaID = c.cuentaID
JOIN formas_pago fp ON ip.formapagoID = fp.formapagoID
UNION ALL
SELECT 
    'EGRESO' as tipo, 
    e.egresoID as movimientoID, 
    e.cajaID, 
    e.empresaID, 
    e.cuentaID, 
    c.codigo as cuenta_codigo, 
    c.nombre as cuenta_nombre, 
    ep.monto, 
    e.concepto, 
    e.fecha, 
    e._estado, 
    ep.formapagoID, 
    fp.tipo as forma_pago, 
    e.entregado, 
    e.recaudacionID, 
    e._usuario as usuarioID
FROM egresos e
JOIN egreso_pagos ep ON e.egresoID = ep.egresoID
JOIN cuentas c ON e.cuentaID = c.cuentaID
JOIN formas_pago fp ON ep.formapagoID = fp.formapagoID";

try {
    $db->ejecutar($sql);
    echo "Vista v_movimientos_caja REDEFINIDA con éxito.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
