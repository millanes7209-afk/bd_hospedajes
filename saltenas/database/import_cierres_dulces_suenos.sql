-- ==============================================================================
-- SCRIPT DE INSERCIÓN DE CIERRES DIARIOS PARA CARRITO "DULCES SUEÑOS"
-- FECHAS: 15/09/2026 AL 22/09/2026 (Excluyendo Lunes 21/09 sin ventas)
-- ASUNCIÓN: 100% Ventas Normales, 0 Sobrantes, 0 Descuadres
-- ==============================================================================

-- 1. Insertar Carrito 'Dulces Sueños' si no existe
INSERT IGNORE INTO `carritos` (`id`, `nombre`, `zona`, `activo`, `created_at`, `updated_at`)
VALUES (1, 'Dulces Sueños', 'Central', 1, NOW(), NOW());

-- 2. Insertar Variante 'Salteña de Carne' si no existe
INSERT IGNORE INTO `variantes_saltena` (`id`, `nombre`, `precio_venta`, `activo`, `created_at`, `updated_at`)
VALUES (1, 'Salteña de Carne', 7.00, 1, NOW(), NOW());

-- 3. Inserción de Cierres Diarios
-- Martes 15/09/2026 (50 uds @ 7.00 = 350.00 Bs, Temp 7-24 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-15', 7.00, 24.00, 350.00, 350.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre1 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre1, 1, 50, 50, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 350.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-15', @cierre1, NOW(), NOW());

-- Miércoles 16/09/2026 (40 uds @ 7.00 = 280.00 Bs, Temp 7-22 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-16', 7.00, 22.00, 280.00, 280.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre2 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre2, 1, 40, 40, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 280.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-16', @cierre2, NOW(), NOW());

-- Jueves 17/09/2026 (50 uds @ 7.00 = 350.00 Bs, Temp 12-27 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-17', 12.00, 27.00, 350.00, 350.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre3 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre3, 1, 50, 50, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 350.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-17', @cierre3, NOW(), NOW());

-- Viernes 18/09/2026 (58 uds @ 7.00 = 406.00 Bs, Temp 11-27 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-18', 11.00, 27.00, 406.00, 406.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre4 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre4, 1, 58, 58, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 406.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-18', @cierre4, NOW(), NOW());

-- Sábado 19/09/2026 (78 uds @ 7.00 = 546.00 Bs, Temp 14-29 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-19', 14.00, 29.00, 546.00, 546.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre5 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre5, 1, 78, 78, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 546.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-19', @cierre5, NOW(), NOW());

-- Domingo 20/09/2026 (47 uds @ 7.00 = 329.00 Bs, Temp 14-32 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-20', 14.00, 32.00, 329.00, 329.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre6 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre6, 1, 47, 47, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 329.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-20', @cierre6, NOW(), NOW());

-- Martes 22/09/2026 (59 uds @ 7.00 = 413.00 Bs, Temp 6-26 °C)
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (1, '2026-09-22', 6.00, 26.00, 413.00, 413.00, 0.00, 0, 'Importación masiva inicial', NOW(), NOW());
SET @cierre7 = LAST_INSERT_ID();
INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@cierre7, 1, 59, 59, 0, 7.00, 0, NOW(), NOW());
INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 413.00, 'Ingreso por Cierre Diario Carrito Dulces Sueños - 2026-09-22', @cierre7, NOW(), NOW());
