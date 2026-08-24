-- Script SQL para inicializar la base de datos de Monaka (monaka-35303439840e)

CREATE TABLE IF NOT EXISTS `categorias` (
  `categoriaID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_modificacion` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`categoriaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `productos` (
  `productoID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoriaID` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`productoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `productos_variantes` (
  `varianteID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `productoID` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`varianteID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedidos` (
  `pedidoID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `numero_pedido` varchar(50) DEFAULT NULL,
  `cliente_nombre` varchar(150) DEFAULT NULL,
  `cliente_telefono` varchar(30) DEFAULT NULL,
  `tipo_pedido` varchar(50) DEFAULT NULL,
  `numero_mesa` varchar(50) DEFAULT NULL,
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `nota` varchar(255) DEFAULT NULL,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` varchar(50) NOT NULL DEFAULT 'pendiente',
  `estado_pago` varchar(50) NOT NULL DEFAULT 'pendiente',
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'ninguno',
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `aceptado_en` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pedidoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pedido_items` (
  `pedidoItemID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedidoID` bigint(20) UNSIGNED NOT NULL,
  `productoID` bigint(20) UNSIGNED DEFAULT NULL,
  `varianteID` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_variante` varchar(150) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`pedidoItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `registros_pedidos` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedidoID` bigint(20) UNSIGNED NOT NULL,
  `evento` varchar(100) NOT NULL,
  `detalles` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mesas` (
  `mesaID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `estado` enum('libre','ocupada') NOT NULL DEFAULT 'libre',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mesaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ventas` (
  `ventaID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `origen` enum('local','whatsapp') NOT NULL DEFAULT 'local',
  `tipo_venta` enum('mesa','llevar','delivery') NOT NULL DEFAULT 'llevar',
  `mesaID` bigint(20) UNSIGNED DEFAULT NULL,
  `cliente_nombre` varchar(150) DEFAULT NULL,
  `cliente_telefono` varchar(30) DEFAULT NULL,
  `direccion_entrega` varchar(255) DEFAULT NULL,
  `nota` varchar(255) DEFAULT NULL,
  `estado` enum('abierta','pendiente','en_preparacion','lista','cerrada') NOT NULL DEFAULT 'abierta',
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usuario_apertura_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `usuario_cierre_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_apertura` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_cierre` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ventaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `venta_items` (
  `ventaItemID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ventaID` bigint(20) UNSIGNED NOT NULL,
  `productoID` bigint(20) UNSIGNED DEFAULT NULL,
  `varianteID` bigint(20) UNSIGNED DEFAULT NULL,
  `nombre_producto` varchar(150) NOT NULL DEFAULT '',
  `nombre_variante` varchar(100) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `nota` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ventaItemID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pagos` (
  `pagoID` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ventaID` bigint(20) UNSIGNED NOT NULL,
  `metodo_pago` enum('qr','efectivo') NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`pagoID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userID` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(50) DEFAULT 'ADMINISTRADOR',
  `rolID` varchar(50) DEFAULT 'ADMINISTRADOR',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
