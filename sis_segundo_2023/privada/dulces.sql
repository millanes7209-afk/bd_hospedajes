-- Estructura de la base de datos: bd_dulces
-- Generado el: 2026-06-01 10:16:51

SET FOREIGN_KEY_CHECKS = 0;
/*
-- Estructura de tabla para `accesos` --
CREATE TABLE `accesos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_acceso` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `id_opcion` int(11) NOT NULL,
  PRIMARY KEY (`id_acceso`),
  KEY `id_rol` (`id_rol`),
  KEY `id_opcion` (`id_opcion`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `cajas` --
CREATE TABLE `cajas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `cajaID` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` enum('ABIERTA','CERRADA') DEFAULT NULL,
  PRIMARY KEY (`cajaID`)
) ENGINE=InnoDB AUTO_INCREMENT=1551 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `cargos` --
CREATE TABLE `cargos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `cargoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `cargo` varchar(20) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  PRIMARY KEY (`cargoID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `cargos_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*
-- Estructura de tabla para `clientes` --
CREATE TABLE `clientes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `clienteID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `ci` varchar(15) NOT NULL,
  `nombres` varchar(30) NOT NULL,
  `apellidos` varchar(30) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `lugar_nacimiento` varchar(15) NOT NULL,
  `est_civil` varchar(15) DEFAULT NULL,
  `profesion` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`clienteID`),
  UNIQUE KEY `ci` (`ci`),
  KEY `empresaID` (`empresaID`),
  KEY `idx_ci` (`ci`),
  KEY `idx_nombres` (`nombres`),
  KEY `idx_apellidos` (`apellidos`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=7924 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
-- Estructura de tabla para `egresos` --
CREATE TABLE `egresos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `egresoID` int(11) NOT NULL AUTO_INCREMENT,
  `monto` float NOT NULL,
  `formaPagoID` int(11) NOT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp(),
  `tipo` varchar(255) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cajaID` int(11) NOT NULL,
  PRIMARY KEY (`egresoID`),
  KEY `formaPagoID` (`formaPagoID`),
  KEY `cajaID` (`cajaID`),
  CONSTRAINT `egresos_ibfk_1` FOREIGN KEY (`formaPagoID`) REFERENCES `formas_pago` (`formaPagoID`),
  CONSTRAINT `egresos_ibfk_2` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`)
) ENGINE=InnoDB AUTO_INCREMENT=1979 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `empresa` --
CREATE TABLE `empresa` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `empresaID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(15) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `logo_agencia` varchar(100) NOT NULL,
  PRIMARY KEY (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `formas_pago` --
CREATE TABLE `formas_pago` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `formaPagoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`formaPagoID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `formas_pago_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `grupos` --
CREATE TABLE `grupos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_grupo` int(11) NOT NULL AUTO_INCREMENT,
  `grupo` varchar(15) NOT NULL,
  PRIMARY KEY (`id_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `habitaciones` --
CREATE TABLE `habitaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `habitacionID` int(11) NOT NULL AUTO_INCREMENT,
  `tipohabitacionID` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `estado` enum('DISPONIBLE','OCUPADA','RESERVADA','MANTENIMIENTO','DEUDA','LIMPIEZA','MOMENTANEO') DEFAULT 'DISPONIBLE',
  `descripcion` varchar(255) DEFAULT NULL,
  `tv` tinyint(1) NOT NULL,
  `bano` tinyint(1) NOT NULL,
  `ventilador` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`habitacionID`),
  KEY `tipohabitacionID` (`tipohabitacionID`),
  CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`tipohabitacionID`) REFERENCES `tipo_habitaciones` (`tipohabitacionID`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
-- Estructura de tabla para `hospedajes` --
CREATE TABLE `hospedajes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `hospedajeID` int(11) NOT NULL AUTO_INCREMENT,
  `hospedaje_anteriorID` int(11) DEFAULT NULL,
  `checkin` datetime DEFAULT current_timestamp(),
  `duracion` int(11) DEFAULT NULL,
  `checkout` datetime NOT NULL,
  `monto_total` float NOT NULL,
  `monto_pagado` float DEFAULT NULL,
  `tipo` enum('MOMENTANEO','HOSPEDAJE','DELEGACIÓN') DEFAULT NULL,
  `monto_pendiente` float NOT NULL,
  `formaPagoID` int(11) DEFAULT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `habitacionID` int(11) NOT NULL,
  `reservaID` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`hospedajeID`),
  KEY `formaPagoID` (`formaPagoID`),
  KEY `habitacionID` (`habitacionID`),
  KEY `reservaID` (`reservaID`),
  CONSTRAINT `hospedajes_ibfk_1` FOREIGN KEY (`formaPagoID`) REFERENCES `formas_pago` (`formaPagoID`),
  CONSTRAINT `hospedajes_ibfk_2` FOREIGN KEY (`habitacionID`) REFERENCES `habitaciones` (`habitacionID`),
  CONSTRAINT `hospedajes_ibfk_3` FOREIGN KEY (`reservaID`) REFERENCES `reservas` (`reservaID`)
) ENGINE=InnoDB AUTO_INCREMENT=20543 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `hospedajes_clientes` --
CREATE TABLE `hospedajes_clientes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `hospedajeID` int(11) NOT NULL,
  `clienteID` int(11) NOT NULL,
  PRIMARY KEY (`hospedajeID`,`clienteID`),
  KEY `clienteID` (`clienteID`),
  CONSTRAINT `hospedajes_clientes_ibfk_1` FOREIGN KEY (`hospedajeID`) REFERENCES `hospedajes` (`hospedajeID`),
  CONSTRAINT `hospedajes_clientes_ibfk_2` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `incidentes` --
CREATE TABLE `incidentes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `incidenteID` int(11) NOT NULL AUTO_INCREMENT,
  `clienteID` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`incidenteID`),
  KEY `clienteID` (`clienteID`),
  CONSTRAINT `incidentes_ibfk_1` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `ingresos` --
CREATE TABLE `ingresos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `ingresoID` int(11) NOT NULL AUTO_INCREMENT,
  `monto` float NOT NULL,
  `formaPagoID` int(11) NOT NULL,
  `fecha_pago` datetime DEFAULT current_timestamp(),
  `reservaID` int(11) DEFAULT NULL,
  `tipo` varchar(255) NOT NULL,
  `hospedajeID` int(11) DEFAULT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cajaID` int(11) NOT NULL,
  PRIMARY KEY (`ingresoID`),
  KEY `cajaID` (`cajaID`),
  KEY `formaPagoID` (`formaPagoID`),
  KEY `hospedajeID` (`hospedajeID`),
  KEY `reservaID` (`reservaID`),
  CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`formaPagoID`) REFERENCES `formas_pago` (`formaPagoID`),
  CONSTRAINT `ingresos_ibfk_3` FOREIGN KEY (`hospedajeID`) REFERENCES `hospedajes` (`hospedajeID`),
  CONSTRAINT `ingresos_ibfk_4` FOREIGN KEY (`reservaID`) REFERENCES `reservas` (`reservaID`)
) ENGINE=InnoDB AUTO_INCREMENT=21464 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `movimientos_caja` --
CREATE TABLE `movimientos_caja` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `movimientocajaID` int(11) NOT NULL AUTO_INCREMENT,
  `cajaID` int(11) NOT NULL,
  `ingresoID` int(11) DEFAULT NULL,
  `egresoID` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('INGRESO','EGRESO') NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `monto` float NOT NULL,
  `formaPagoID` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`movimientocajaID`),
  KEY `ingresoID` (`ingresoID`),
  KEY `formaPagoID` (`formaPagoID`),
  KEY `cajaID` (`cajaID`),
  KEY `egresoID` (`egresoID`),
  CONSTRAINT `movimientos_caja_ibfk_1` FOREIGN KEY (`ingresoID`) REFERENCES `ingresos` (`ingresoID`),
  CONSTRAINT `movimientos_caja_ibfk_2` FOREIGN KEY (`formaPagoID`) REFERENCES `formas_pago` (`formaPagoID`),
  CONSTRAINT `movimientos_caja_ibfk_3` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `movimientos_caja_ibfk_4` FOREIGN KEY (`egresoID`) REFERENCES `egresos` (`egresoID`)
) ENGINE=InnoDB AUTO_INCREMENT=23430 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*
-- Estructura de tabla para `movimientos_sueldo` --
CREATE TABLE `movimientos_sueldo` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `movimientoID` int(11) NOT NULL AUTO_INCREMENT,
  `propietarioID` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `tipo_movimiento` enum('SUELDO','ADELANTO') NOT NULL,
  `monto` float NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `estado` enum('PENDIENTE','ACEPTADO','RECHAZADO') DEFAULT 'PENDIENTE',
  PRIMARY KEY (`movimientoID`),
  KEY `id_persona` (`id_persona`),
  KEY `propietarioID` (`propietarioID`),
  CONSTRAINT `movimientos_sueldo_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`),
  CONSTRAINT `movimientos_sueldo_ibfk_2` FOREIGN KEY (`propietarioID`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `notificaciones` --
CREATE TABLE `notificaciones` (
  `notificacionID` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('pendiente','atendida') DEFAULT 'pendiente',
  `fecha_programada` datetime NOT NULL,
  `tipo` enum('emitir_factura') DEFAULT 'emitir_factura',
  PRIMARY KEY (`notificacionID`)
) ENGINE=MyISAM AUTO_INCREMENT=1387 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `opciones` --
CREATE TABLE `opciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_opcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_grupo` int(11) NOT NULL,
  `opcion` varchar(100) NOT NULL,
  `contenido` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL,
  PRIMARY KEY (`id_opcion`),
  KEY `id_grupo` (`id_grupo`),
  CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `personas` --
CREATE TABLE `personas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_persona` int(11) NOT NULL AUTO_INCREMENT,
  `cargoID` int(11) DEFAULT NULL,
  `ci` varchar(15) NOT NULL,
  `nombres` varchar(40) NOT NULL,
  `ap` varchar(20) NOT NULL,
  `am` varchar(20) DEFAULT NULL,
  `telefono` varchar(15) NOT NULL,
  `direccion` varchar(200) DEFAULT NULL,
  `genero` char(1) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `sueldo` float DEFAULT NULL,
  PRIMARY KEY (`id_persona`),
  UNIQUE KEY `ci` (`ci`),
  KEY `cargoID` (`cargoID`),
  CONSTRAINT `personas_ibfk_1` FOREIGN KEY (`cargoID`) REFERENCES `cargos` (`cargoID`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `reservas` --
CREATE TABLE `reservas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `reservaID` int(11) NOT NULL AUTO_INCREMENT,
  `clienteID` int(11) DEFAULT NULL,
  `habitacionID` int(11) NOT NULL,
  `formaPagoID` int(11) DEFAULT NULL,
  `fecha_reserva` datetime DEFAULT current_timestamp(),
  `checkin` datetime NOT NULL,
  `monto_reserva` float NOT NULL,
  `monto_pagado` float DEFAULT 0,
  `monto_pendiente` float DEFAULT NULL,
  `estado` enum('PENDIENTE','CONFIRMADA','CANCELADA') DEFAULT 'PENDIENTE',
  `estado2` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`reservaID`),
  KEY `clienteID` (`clienteID`),
  KEY `formaPagoID` (`formaPagoID`),
  KEY `habitacionID` (`habitacionID`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`formaPagoID`) REFERENCES `formas_pago` (`formaPagoID`),
  CONSTRAINT `reservas_ibfk_3` FOREIGN KEY (`habitacionID`) REFERENCES `habitaciones` (`habitacionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `roles` --
CREATE TABLE `roles` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) NOT NULL,
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `tipo_habitaciones` --
CREATE TABLE `tipo_habitaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `tipohabitacionID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` float NOT NULL,
  PRIMARY KEY (`tipohabitacionID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `tipo_habitaciones_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `usuarios` --
CREATE TABLE `usuarios` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_persona` int(11) NOT NULL,
  `usuario` varchar(15) NOT NULL,
  `clave` varchar(200) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `id_persona` (`id_persona`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `usuarios_roles` --
CREATE TABLE `usuarios_roles` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `id_usuario_rol` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario_rol`),
  KEY `id_rol` (`id_rol`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`),
  CONSTRAINT `usuarios_roles_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `visitas` --
CREATE TABLE `visitas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `visitaID` int(11) NOT NULL AUTO_INCREMENT,
  `clienteID` int(11) NOT NULL,
  `hospedajeID` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`visitaID`),
  KEY `hospedajeID` (`hospedajeID`),
  KEY `clienteID` (`clienteID`),
  CONSTRAINT `visitas_ibfk_1` FOREIGN KEY (`hospedajeID`) REFERENCES `hospedajes` (`hospedajeID`),
  CONSTRAINT `visitas_ibfk_2` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`)
) ENGINE=InnoDB AUTO_INCREMENT=18428 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
*/