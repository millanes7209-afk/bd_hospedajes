-- ==============================================================================
-- CONSULTAS SQL PARA INSERTAR LOS CIERRES DIARIOS DEL CARRITO "DULCES SUEÑOS"
-- PRECIO POR SALTEÑA: 3.50 BS.
-- FECHAS: 15/09/2026 AL 22/09/2026 (Excluyendo Lunes 21/09 sin ventas)
-- ==============================================================================

-- 1. Asegurar la existencia del Carrito 'Dulces Sueños'
INSERT IGNORE INTO `carritos` (`id`, `nombre`, `zona`, `activo`, `created_at`, `updated_at`)
VALUES (1, 'Dulces Sueños', 'Central', 1, NOW(), NOW());

-- 2. Asegurar la existencia de la Variante 'Salteña de Carne' con precio de 3.50 Bs.
INSERT INTO `variantes_saltena` (`id`, `nombre`, `precio_venta`, `activo`, `created_at`, `updated_at`)
VALUES (1, 'Salteña de Carne', 3.50, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `precio_venta` = 3.50;

-- ------------------------------------------------------------------------------
-- Martes 15/09/2026 (50 salteñas @ 3.50 Bs = 175.00 Bs | Temp: 7°C - 24°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-15', 7.00, 24.00, 175.00, 175.00, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c1 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c1, 1, 50, 50, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 175.00, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-15)', @c1, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Miércoles 16/09/2026 (40 salteñas @ 3.50 Bs = 140.00 Bs | Temp: 7°C - 22°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-16', 7.00, 22.00, 140.00, 140.00, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c2 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c2, 1, 40, 40, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 140.00, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-16)', @c2, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Jueves 17/09/2026 (50 salteñas @ 3.50 Bs = 175.00 Bs | Temp: 12°C - 27°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-17', 12.00, 27.00, 175.00, 175.00, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c3 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c3, 1, 50, 50, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 175.00, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-17)', @c3, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Viernes 18/09/2026 (58 salteñas @ 3.50 Bs = 203.00 Bs | Temp: 11°C - 27°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-18', 11.00, 27.00, 203.00, 203.00, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c4 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c4, 1, 58, 58, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 203.00, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-18)', @c4, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Sábado 19/09/2026 (78 salteñas @ 3.50 Bs = 273.00 Bs | Temp: 14°C - 29°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-19', 14.00, 29.00, 273.00, 273.00, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c5 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c5, 1, 78, 78, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 273.00, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-19)', @c5, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Domingo 20/09/2026 (47 salteñas @ 3.50 Bs = 164.50 Bs | Temp: 14°C - 32°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-20', 14.00, 32.00, 164.50, 164.50, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c6 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c6, 1, 47, 47, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 164.50, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-20)', @c6, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Martes 22/09/2026 (59 salteñas @ 3.50 Bs = 206.50 Bs | Temp: 6°C - 26°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-22', 6.00, 26.00, 206.50, 206.50, 0.00, 0, 'Importación masiva', NOW(), NOW());
SET @c7 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@c7, 1, 59, 59, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 206.50, 'Ingreso Cierre Diario - Dulces Sueños (2026-09-22)', @c7, NOW(), NOW());
