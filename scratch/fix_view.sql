CREATE OR REPLACE VIEW bd_hospedajes.v_movimientos_caja AS
SELECT 
    'INGRESO' AS tipo,
    i.ingresoID AS movimientoID,
    i.cajaID AS cajaID,
    i.empresaID AS empresaID,
    i._usuario AS usuarioID,
    i.cuentaID AS cuentaID,
    c.codigo AS cuenta_codigo,
    c.nombre AS cuenta_nombre,
    COALESCE(ip.monto, i.monto_total) AS monto,
    i.concepto AS concepto,
    i.fecha AS fecha,
    i._estado AS _estado,
    i.recaudacionID AS recaudacionID,
    fp.tipo AS forma_pago,
    i._fec_insercion AS _fec_insercion
FROM bd_hospedajes.ingresos i
JOIN bd_hospedajes.cuentas c ON i.cuentaID = c.cuentaID
LEFT JOIN bd_hospedajes.ingreso_pagos ip ON i.ingresoID = ip.ingresoID
LEFT JOIN bd_hospedajes.formas_pago fp ON ip.formapagoID = fp.formapagoID
UNION ALL
SELECT 
    'EGRESO' AS tipo,
    e.egresoID AS movimientoID,
    e.cajaID AS cajaID,
    e.empresaID AS empresaID,
    e._usuario AS usuarioID,
    e.cuentaID AS cuentaID,
    c.codigo AS cuenta_codigo,
    c.nombre AS cuenta_nombre,
    COALESCE(ep.monto, e.monto_total) AS monto,
    e.concepto AS concepto,
    e.fecha AS fecha,
    e._estado AS _estado,
    NULL AS recaudacionID,
    fp.tipo AS forma_pago,
    e._fec_insercion AS _fec_insercion
FROM bd_hospedajes.egresos e
JOIN bd_hospedajes.cuentas c ON e.cuentaID = c.cuentaID
LEFT JOIN bd_hospedajes.egreso_pagos ep ON e.egresoID = ep.egresoID
LEFT JOIN bd_hospedajes.formas_pago fp ON ep.formapagoID = fp.formapagoID;
