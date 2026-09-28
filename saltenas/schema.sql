-- ============================================================
-- ESTRUCTURA DE BASE DE DATOS - SISTEMA SALTEÑAS
-- MySQL / MariaDB DDL Schema Script
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `boveda_movimientos`;
DROP TABLE IF EXISTS `cierre_diario_promocion_detalle`;
DROP TABLE IF EXISTS `cierre_diario_detalle`;
DROP TABLE IF EXISTS `cierres_diarios`;
DROP TABLE IF EXISTS `promociones`;
DROP TABLE IF EXISTS `variante_receta`;
DROP TABLE IF EXISTS `variantes_saltena`;
DROP TABLE IF EXISTS `preparacion_receta`;
DROP TABLE IF EXISTS `preparaciones`;
DROP TABLE IF EXISTS `compra_detalle`;
DROP TABLE IF EXISTS `compras`;
DROP TABLE IF EXISTS `insumo_precios_historial`;
DROP TABLE IF EXISTS `insumos`;
DROP TABLE IF EXISTS `carritos`;
DROP TABLE IF EXISTS `users`;

-- 1. TABLA: users
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABLA: carritos (Puntos de Venta)
CREATE TABLE `carritos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `zona` VARCHAR(255) NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABLA: insumos
CREATE TABLE `insumos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `unidad_medida` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABLA: insumo_precios_historial (Generado Automáticamente por Compras)
CREATE TABLE `insumo_precios_historial` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `insumo_id` BIGINT UNSIGNED NOT NULL,
  `precio` DECIMAL(10,2) NOT NULL,
  `vigente_desde` DATE NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_iph_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABLA: compras
CREATE TABLE `compras` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha` DATE NOT NULL,
  `monto_total` DECIMAL(10,2) NOT NULL,
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABLA: compra_detalle
CREATE TABLE `compra_detalle` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `compra_id` BIGINT UNSIGNED NOT NULL,
  `insumo_id` BIGINT UNSIGNED NOT NULL,
  `cantidad` DECIMAL(10,2) NOT NULL,
  `precio_unitario` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cd_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cd_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABLA: preparaciones (ej. Masa)
CREATE TABLE `preparaciones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `rinde_cantidad` DECIMAL(10,2) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. TABLA: preparacion_receta
CREATE TABLE `preparacion_receta` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `preparacion_id` BIGINT UNSIGNED NOT NULL,
  `insumo_id` BIGINT UNSIGNED NOT NULL,
  `cantidad_usada` DECIMAL(10,4) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pr_preparacion` FOREIGN KEY (`preparacion_id`) REFERENCES `preparaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pr_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. TABLA: variantes_saltena (Variantes de Salteñas)
CREATE TABLE `variantes_saltena` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `precio_venta` DECIMAL(10,2) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. TABLA: variante_receta (Composición de Receta: Insumos + Preparaciones)
CREATE TABLE `variante_receta` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `variante_id` BIGINT UNSIGNED NOT NULL,
  `tipo_componente` ENUM('insumo', 'preparacion') NOT NULL,
  `insumo_id` BIGINT UNSIGNED NULL,
  `preparacion_id` BIGINT UNSIGNED NULL,
  `cantidad_usada` DECIMAL(10,4) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_vr_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_saltena` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vr_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_vr_preparacion` FOREIGN KEY (`preparacion_id`) REFERENCES `preparaciones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. TABLA: promociones (Combos Explícitos)
CREATE TABLE `promociones` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `variante_id` BIGINT UNSIGNED NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `unidades_por_paquete` INT NOT NULL,
  `precio_paquete` DECIMAL(10,2) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_promo_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_saltena` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. TABLA: cierres_diarios
CREATE TABLE `cierres_diarios` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `carrito_id` BIGINT UNSIGNED NOT NULL,
  `fecha` DATE NOT NULL,
  `temp_min` DECIMAL(5,2) NULL,
  `temp_max` DECIMAL(5,2) NULL,
  `monto_real` DECIMAL(10,2) NOT NULL,
  `monto_estimado` DECIMAL(10,2) NOT NULL,
  `diferencia` DECIMAL(10,2) NOT NULL,
  `inconsistente` TINYINT(1) NOT NULL DEFAULT 0,
  `observaciones` TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cdiario_carrito` FOREIGN KEY (`carrito_id`) REFERENCES `carritos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. TABLA: cierre_diario_detalle
CREATE TABLE `cierre_diario_detalle` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cierre_diario_id` BIGINT UNSIGNED NOT NULL,
  `variante_id` BIGINT UNSIGNED NOT NULL,
  `cantidad_entregada` INT NOT NULL,
  `cantidad_vendida_normal` INT NOT NULL,
  `cantidad_sobrante` INT NOT NULL,
  `precio_unitario_aplicado` DECIMAL(10,2) NOT NULL,
  `inconsistente` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cdd_cierre` FOREIGN KEY (`cierre_diario_id`) REFERENCES `cierres_diarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cdd_variante` FOREIGN KEY (`variante_id`) REFERENCES `variantes_saltena` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. TABLA: cierre_diario_promocion_detalle
CREATE TABLE `cierre_diario_promocion_detalle` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cierre_diario_detalle_id` BIGINT UNSIGNED NOT NULL,
  `promocion_id` BIGINT UNSIGNED NOT NULL,
  `paquetes_vendidos` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_cdpd_detalle` FOREIGN KEY (`cierre_diario_detalle_id`) REFERENCES `cierre_diario_detalle` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cdpd_promo` FOREIGN KEY (`promocion_id`) REFERENCES `promociones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. TABLA: boveda_movimientos (Caja Central)
CREATE TABLE `boveda_movimientos` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('ingreso', 'egreso') NOT NULL,
  `monto` DECIMAL(10,2) NOT NULL,
  `fecha` DATE NOT NULL,
  `cierre_diario_id` BIGINT UNSIGNED NULL,
  `compra_id` BIGINT UNSIGNED NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_bm_cierre` FOREIGN KEY (`cierre_diario_id`) REFERENCES `cierres_diarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bm_compra` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. USUARIO ADMINISTRADOR INICIAL
INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`)
VALUES (1, 'Administrador', 'millanes7209@gmail.com', '$2y$10$xMdfLI1n/sxWB0jIThBKRuEaq/cFC4yR8zcxDR5yLU7ceKFNtSKxK', NOW(), NOW())
ON DUPLICATE KEY UPDATE `password` = VALUES(`password`);

-- 17. DATOS INICIALES (CARRITOS Y VARIANTES)
INSERT INTO `carritos` (`id`, `nombre`, `zona`, `activo`, `created_at`, `updated_at`)
VALUES 
(1, 'CARRITO 1 - PEREZ VELASCO', 'CENTRO', 1, NOW(), NOW()),
(2, 'CARRITO 2 - SAN FRANCISCO', 'CENTRO', 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

INSERT INTO `variantes_saltena` (`id`, `nombre`, `precio_venta`, `activo`, `created_at`, `updated_at`)
VALUES 
(1, 'SALTEÑA DE POLLO', 8.00, 1, NOW(), NOW()),
(2, 'SALTEÑA DE CARNE', 8.00, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

SET FOREIGN_KEY_CHECKS = 1;


