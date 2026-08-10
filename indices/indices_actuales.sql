-- ==========================================================
-- REPORTE DE ÍNDICES ACTUALES EN EL HOST (alloggibolivia.com)
-- ==========================================================
-- Generado vía Auditoría de Sistema - 27/06/2026

-- TABLA: accesos
Table: accesos | Index: PRIMARY | Column: accesoID | Uniq: YES

-- TABLA: auditorias
Table: auditorias | Index: empresaID | Column: empresaID | Uniq: NO
Table: auditorias | Index: estado_revision | Column: estado_revision | Uniq: NO
Table: auditorias | Index: hospedajeID | Column: hospedajeID | Uniq: NO
Table: auditorias | Index: PRIMARY | Column: auditoriaID | Uniq: YES

-- TABLA: banos
Table: banos | Index: PRIMARY | Column: banoID | Uniq: YES

-- TABLA: cajas
Table: cajas | Index: empresaID | Column: empresaID | Uniq: NO
Table: cajas | Index: PRIMARY | Column: cajaID | Uniq: YES
Table: cajas | Index: usuarioID | Column: usuarioID | Uniq: NO

-- TABLA: cierre_cajas
Table: cierre_cajas | Index: cajaID | Column: cajaID | Uniq: NO
Table: cierre_cajas | Index: formapagoID | Column: formapagoID | Uniq: NO
Table: cierre_cajas | Index: PRIMARY | Column: cierrecajaID | Uniq: YES

-- TABLA: clientes
Table: clientes | Index: ci_pais_unico | Column: ci | Uniq: YES
Table: clientes | Index: ci_pais_unico | Column: paisID | Uniq: YES
Table: clientes | Index: idx_ci | Column: ci | Uniq: NO
Table: clientes | Index: idx_clientes_apellidos | Column: apellido1 | Uniq: NO
Table: clientes | Index: idx_clientes_apellidos | Column: apellido2 | Uniq: NO
Table: clientes | Index: idx_clientes_nombres | Column: nombres | Uniq: NO
Table: clientes | Index: PRIMARY | Column: clienteID | Uniq: YES

-- TABLA: cuentas
Table: cuentas | Index: empresaID | Column: empresaID | Uniq: NO
Table: cuentas | Index: PRIMARY | Column: cuentaID | Uniq: YES

-- TABLA: egresos
Table: egresos | Index: cuentaID | Column: cuentaID | Uniq: NO
Table: egresos | Index: empresaID | Column: empresaID | Uniq: NO
Table: egresos | Index: fk_egreso_recaudacion | Column: recaudacionID | Uniq: NO
Table: egresos | Index: idx_caja_empresa | Column: cajaID | Uniq: NO

-- TABLA: ingresos
Table: ingresos | Index: PRIMARY | Column: ingresoID | Uniq: YES
Table: ingresos | Index: cuentaID | Column: cuentaID | Uniq: NO
Table: ingresos | Index: fk_ingreso_recaudacion | Column: recaudacionID | Uniq: NO
Table: ingresos | Index: idx_caja_empresa | Column: cajaID | Uniq: NO
Table: ingresos | Index: idx_caja_empresa | Column: empresaID | Uniq: NO
Table: ingresos | Index: idx_fecha | Column: fecha | Uniq: NO
Table: ingresos | Index: idx_stats_ingresos | Column: empresaID | Uniq: NO
Table: ingresos | Index: idx_stats_ingresos | Column: fecha | Uniq: NO

-- ==========================================================
-- FIN DEL REPORTE
-- ==========================================================
