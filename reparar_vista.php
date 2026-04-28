<?php
require_once("conexion.php");

$sql = "CREATE OR REPLACE VIEW v_movimientos_caja AS
        -- PARTE 1: INGRESOS
        SELECT 
            i.ingresoID AS movimientoID,
            i.cajaID,
            i.empresaID,
            i.usuarioID,
            'INGRESO' AS tipo,
            i.concepto,
            c.nombre AS cuenta_nombre,
            fp.tipo AS forma_pago,
            ip.monto,
            i.fecha,
            i._estado
        FROM ingresos i
        INNER JOIN ingreso_pagos ip ON i.ingresoID = ip.ingresoID
        INNER JOIN formas_pago fp ON ip.formapagoID = fp.formapagoID
        INNER JOIN cuentas c ON i.cuentaID = c.cuentaID
        WHERE i._estado <> 'X'

        UNION ALL

        -- PARTE 2: EGRESOS
        SELECT 
            e.egresoID AS movimientoID,
            e.cajaID,
            e.empresaID,
            e.usuarioID,
            'EGRESO' AS tipo,
            e.concepto,
            c.nombre AS cuenta_nombre,
            fp.tipo AS forma_pago,
            ep.monto,
            e.fecha,
            e._estado
        FROM egresos e
        INNER JOIN egreso_pagos ep ON e.egresoID = ep.egresoID
        INNER JOIN formas_pago fp ON ep.formapagoID = fp.formapagoID
        INNER JOIN cuentas c ON e.cuentaID = c.cuentaID
        WHERE e._estado <> 'X'";

if ($db->ejecutar($sql) !== false) {
    echo "VISTA REPARADA: Ahora incluye cuenta_nombre y usuarioID.";
} else {
    echo "ERROR AL REPARAR LA VISTA.";
}
?>
