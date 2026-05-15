-- Corrección de colación en la vista v_movimientos_caja
-- El error: Illegal mix of collations (utf8mb4_unicode_ci,COERCIBLE) and (utf8mb4_general_ci,COERCIBLE) for operation '='

-- Solución: Forzar la misma colación en todas las columnas de texto

-- Opción 1: Forzar utf8mb4_unicode_ci en todas partes
CREATE OR REPLACE VIEW bd_hospedajes.v_movimientos_caja AS
SELECT 
    'INGRESO' AS tipo,
    i.ingresoID AS movimientoID,
    i.cajaID AS cajaID,
    i.empresaID AS empresaID,
    i._usuario AS usuarioID,
    i.cuentaID AS cuentaID,
    c.codigo AS cuenta_codigo,
    CONVERT(c.nombre USING utf8mb4) COLLATE utf8mb4_unicode_ci AS cuenta_nombre,
    COALESCE(ip.monto, i.monto_total) AS monto,
    CONVERT(i.concepto USING utf8mb4) COLLATE utf8mb4_unicode_ci AS concepto,
    i.fecha AS fecha,
    i._estado AS _estado,
    i.recaudacionID AS recaudacionID,
    CONVERT(fp.tipo USING utf8mb4) COLLATE utf8mb4_unicode_ci AS forma_pago,
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
    CONVERT(c.nombre USING utf8mb4) COLLATE utf8mb4_unicode_ci AS cuenta_nombre,
    COALESCE(ep.monto, e.monto_total) AS monto,
    CONVERT(e.concepto USING utf8mb4) COLLATE utf8mb4_unicode_ci AS concepto,
    e.fecha AS fecha,
    e._estado AS _estado,
    NULL AS recaudacionID,
    CONVERT(fp.tipo USING utf8mb4) COLLATE utf8mb4_unicode_ci AS forma_pago,
    e._fec_insercion AS _fec_insercion
FROM bd_hospedajes.egresos e
JOIN bd_hospedajes.cuentas c ON e.cuentaID = c.cuentaID
LEFT JOIN bd_hospedajes.egreso_pagos ep ON e.egresoID = ep.egresoID
LEFT JOIN bd_hospedajes.formas_pago fp ON ep.formapagoID = fp.formapagoID;
