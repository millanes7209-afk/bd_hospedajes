-- ==============================================================================
-- CONSULTAS SQL ÚNICAMENTE PARA INSERTAR CIERRES DIARIOS (PRECIO: 3.50 BS)
-- UTILIZA TUS REGISTROS EXISTENTES DE CARRITO Y VARIANTE
-- ==============================================================================

-- Reemplaza los números con los ID reales de tu carrito y variante existentes:
SET @carrito_id = 1;  -- ID real de tu Carrito Dulces Sueños
SET @variante_id = 1; -- ID real de tu Variante de Salteña

-- ------------------------------------------------------------------------------
-- Martes 15/09/2026 (50 salteñas @ 3.50 Bs = 175.00 Bs | Temp: 7°C - 24°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-15', 7.00, 24.00, 175.00, 175.00, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id1 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id1, @variante_id, 50, 50, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 175.00, 'Ingreso Cierre Diario - 2026-09-15', @id1, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Miércoles 16/09/2026 (40 salteñas @ 3.50 Bs = 140.00 Bs | Temp: 7°C - 22°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-16', 7.00, 22.00, 140.00, 140.00, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id2 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id2, @variante_id, 40, 40, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 140.00, 'Ingreso Cierre Diario - 2026-09-16', @id2, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Jueves 17/09/2026 (50 salteñas @ 3.50 Bs = 175.00 Bs | Temp: 12°C - 27°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-17', 12.00, 27.00, 175.00, 175.00, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id3 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id3, @variante_id, 50, 50, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 175.00, 'Ingreso Cierre Diario - 2026-09-17', @id3, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Viernes 18/09/2026 (58 salteñas @ 3.50 Bs = 203.00 Bs | Temp: 11°C - 27°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-18', 11.00, 27.00, 203.00, 203.00, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id4 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id4, @variante_id, 58, 58, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 203.00, 'Ingreso Cierre Diario - 2026-09-18', @id4, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Sábado 19/09/2026 (78 salteñas @ 3.50 Bs = 273.00 Bs | Temp: 14°C - 29°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-19', 14.00, 29.00, 273.00, 273.00, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id5 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id5, @variante_id, 78, 78, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 273.00, 'Ingreso Cierre Diario - 2026-09-19', @id5, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Domingo 20/09/2026 (47 salteñas @ 3.50 Bs = 164.50 Bs | Temp: 14°C - 32°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-20', 14.00, 32.00, 164.50, 164.50, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id6 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id6, @variante_id, 47, 47, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 164.50, 'Ingreso Cierre Diario - 2026-09-20', @id6, NOW(), NOW());

-- ------------------------------------------------------------------------------
-- Martes 22/09/2026 (59 salteñas @ 3.50 Bs = 206.50 Bs | Temp: 6°C - 26°C)
-- ------------------------------------------------------------------------------
INSERT INTO `cierres_diarios` (`carrito_id`, `fecha`, `temp_min`, `temp_max`, `monto_real`, `monto_estimado`, `diferencia`, `inconsistente`, `observaciones`, `created_at`, `updated_at`)
VALUES (@carrito_id, '2026-09-22', 6.00, 26.00, 206.50, 206.50, 0.00, 0, 'Cierre Diario', NOW(), NOW());
SET @id7 = LAST_INSERT_ID();

INSERT INTO `cierre_diario_detalle` (`cierre_diario_id`, `variante_id`, `cantidad_entregada`, `cantidad_vendida_normal`, `cantidad_sobrante`, `precio_unitario_aplicado`, `inconsistente`, `created_at`, `updated_at`)
VALUES (@id7, @variante_id, 59, 59, 0, 3.50, 0, NOW(), NOW());

INSERT INTO `boveda_movimientos` (`tipo`, `monto`, `concepto`, `cierre_diario_id`, `created_at`, `updated_at`)
VALUES ('INGRESO', 206.50, 'Ingreso Cierre Diario - 2026-09-22', @id7, NOW(), NOW());
