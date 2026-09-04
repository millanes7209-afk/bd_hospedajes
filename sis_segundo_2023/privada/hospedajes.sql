-- Estructura de la base de datos: bd_hospedajes
-- Generado el: 2026-06-01 10:16:51

SET FOREIGN_KEY_CHECKS = 0;
/*
-- Estructura de tabla para `accesos` --
CREATE TABLE `accesos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `accesoID` int(11) NOT NULL AUTO_INCREMENT,
  `rolID` int(11) NOT NULL,
  `opcionID` int(11) NOT NULL,
  PRIMARY KEY (`accesoID`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `auditorias` --
CREATE TABLE `auditorias` (
  `auditoriaID` int(11) NOT NULL AUTO_INCREMENT,
  `hospedajeID` int(11) NOT NULL,
  `tipo_auditoria` enum('MODIFICACION','ELIMINACION') DEFAULT 'MODIFICACION',
  `monto_anterior` decimal(15,2) NOT NULL,
  `monto_nuevo` decimal(15,2) NOT NULL,
  `detalle_original` text DEFAULT NULL,
  `detalle_nuevo` text DEFAULT NULL,
  `estado_revision` tinyint(4) DEFAULT 0,
  `empresaID` int(11) NOT NULL,
  `motivo` text DEFAULT NULL,
  `usuarioID` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`auditoriaID`),
  KEY `hospedajeID` (`hospedajeID`),
  KEY `empresaID` (`empresaID`),
  KEY `estado_revision` (`estado_revision`),
  CONSTRAINT `fk_auditoria_hospedaje` FOREIGN KEY (`hospedajeID`) REFERENCES `hospedajes` (`hospedajeID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `banos` --
CREATE TABLE `banos` (
  `banoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `cajaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `entregado` tinyint(1) DEFAULT 0,
  `recaudacionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`banoID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*
-- Estructura de tabla para `cajas` --
CREATE TABLE `cajas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `cajaID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `estado` enum('ABIERTA','CERRADA') DEFAULT 'CERRADA',
  `fecha_apertura` datetime DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`cajaID`),
  KEY `empresaID` (`empresaID`),
  KEY `usuarioID` (`usuarioID`),
  CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `cajas_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`)
) ENGINE=InnoDB AUTO_INCREMENT=905 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
-- Estructura de tabla para `cierre_cajas` --
CREATE TABLE `cierre_cajas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `cierrecajaID` int(11) NOT NULL AUTO_INCREMENT,
  `cajaID` int(11) NOT NULL,
  `formapagoID` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  PRIMARY KEY (`cierrecajaID`),
  KEY `cajaID` (`cajaID`),
  KEY `formapagoID` (`formapagoID`),
  CONSTRAINT `cierre_cajas_ibfk_1` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `cierre_cajas_ibfk_2` FOREIGN KEY (`formapagoID`) REFERENCES `formas_pago` (`formapagoID`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*
-- Estructura de tabla para `clientes` --
CREATE TABLE `clientes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `clienteID` int(11) NOT NULL AUTO_INCREMENT,
  `ci` varchar(15) NOT NULL,
  `nombres` varchar(50) NOT NULL,
  `apellido1` varchar(30) NOT NULL,
  `apellido2` varchar(30) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `lugar_nacimiento` varchar(100) NOT NULL,
  `estado_civil` varchar(25) DEFAULT NULL,
  `profesion` varchar(100) DEFAULT NULL,
  `paisID` int(11) NOT NULL,
  PRIMARY KEY (`clienteID`),
  UNIQUE KEY `ci_pais_unico` (`ci`,`paisID`),
  KEY `idx_ci` (`ci`)
) ENGINE=InnoDB AUTO_INCREMENT=6216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
-- Estructura de tabla para `cuentas` --
CREATE TABLE `cuentas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `cuentaID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  PRIMARY KEY (`cuentaID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `cuentas_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `egresos` --
CREATE TABLE `egresos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `egresoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `cajaID` int(11) NOT NULL,
  `cuentaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `concepto` varchar(200) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `entregado` tinyint(1) DEFAULT 0,
  `fecha_entrega` datetime DEFAULT NULL,
  `recaudacionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`egresoID`),
  KEY `empresaID` (`empresaID`),
  KEY `cuentaID` (`cuentaID`),
  KEY `fk_egreso_recaudacion` (`recaudacionID`),
  KEY `idx_caja_empresa` (`cajaID`,`empresaID`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `egresos_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `egresos_ibfk_2` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `egresos_ibfk_3` FOREIGN KEY (`cuentaID`) REFERENCES `cuentas` (`cuentaID`),
  CONSTRAINT `fk_egreso_recaudacion` FOREIGN KEY (`recaudacionID`) REFERENCES `recaudaciones` (`recaudacionID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `egreso_pagos` --
CREATE TABLE `egreso_pagos` (
  `egresopagoID` int(11) NOT NULL AUTO_INCREMENT,
  `egresoID` int(11) NOT NULL,
  `formapagoID` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  PRIMARY KEY (`egresopagoID`),
  KEY `egresoID` (`egresoID`),
  KEY `formapagoID` (`formapagoID`),
  CONSTRAINT `egreso_pagos_ibfk_1` FOREIGN KEY (`egresoID`) REFERENCES `egresos` (`egresoID`),
  CONSTRAINT `egreso_pagos_ibfk_2` FOREIGN KEY (`formapagoID`) REFERENCES `formas_pago` (`formapagoID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `empleados` --
CREATE TABLE `empleados` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `empleadoID` int(11) NOT NULL AUTO_INCREMENT,
  `ci` varchar(15) NOT NULL,
  `nombres` varchar(40) NOT NULL,
  `apellidos` varchar(40) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `genero` char(1) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  PRIMARY KEY (`empleadoID`),
  UNIQUE KEY `ci` (`ci`),
  UNIQUE KEY `idx_ci_unico` (`ci`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `empleado_empresas` --
CREATE TABLE `empleado_empresas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `empleadoempresaID` int(11) NOT NULL AUTO_INCREMENT,
  `empleadoID` int(11) NOT NULL,
  `rolID` int(11) NOT NULL,
  `empresaID` int(11) NOT NULL,
  `sueldo` decimal(15,2) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `es_titular` tinyint(1) DEFAULT 0,
  `estado_laboral` enum('ACTIVO','INACTIVO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
  PRIMARY KEY (`empleadoempresaID`),
  KEY `empleadoID` (`empleadoID`),
  KEY `empresaID` (`empresaID`),
  KEY `fk_empempresa_rol` (`rolID`),
  CONSTRAINT `empleado_empresas_ibfk_1` FOREIGN KEY (`empleadoID`) REFERENCES `empleados` (`empleadoID`) ON DELETE CASCADE,
  CONSTRAINT `empleado_empresas_ibfk_2` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`) ON DELETE CASCADE,
  CONSTRAINT `fk_empempresa_rol` FOREIGN KEY (`rolID`) REFERENCES `roles` (`rolID`)
) ENGINE=InnoDB AUTO_INCREMENT=1035 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `empresa` --
CREATE TABLE `empresa` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `empresaID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `logo_agencia` varchar(100) NOT NULL,
  `color_primario` varchar(10) DEFAULT '#059669',
  `color_secundario` varchar(10) DEFAULT '#ffffff',
  `ruc` varchar(20) DEFAULT NULL,
  `representante_legal` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`empresaID`),
  KEY `idx_empresa_estado` (`_estado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `empresa_funcionalidades` --
CREATE TABLE `empresa_funcionalidades` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `empresafuncionID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `funcionalidadID` int(11) NOT NULL,
  `fecha_activacion` date DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('ACTIVO','VENCIDO','CANCELADO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`empresafuncionID`),
  KEY `funcionalidadID` (`funcionalidadID`),
  KEY `idx_empfunc_emp_func` (`empresaID`,`funcionalidadID`),
  CONSTRAINT `empresa_funcionalidades_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`) ON DELETE CASCADE,
  CONSTRAINT `empresa_funcionalidades_ibfk_2` FOREIGN KEY (`funcionalidadID`) REFERENCES `funcionalidades` (`funcionalidadID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `formas_pago` --
CREATE TABLE `formas_pago` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `formapagoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`formapagoID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `formas_pago_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `funcionalidades` --
CREATE TABLE `funcionalidades` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `funcionalidadID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(30) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  PRIMARY KEY (`funcionalidadID`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `grupos` --
CREATE TABLE `grupos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `grupoID` int(11) NOT NULL AUTO_INCREMENT,
  `grupo` varchar(30) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`grupoID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `habitaciones` --
CREATE TABLE `habitaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `habitacionID` int(11) NOT NULL AUTO_INCREMENT,
  `tipohabitacionID` int(11) NOT NULL,
  `empresaID` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `estado` enum('DISPONIBLE','OCUPADA','LIMPIEZA','MANTENIMIENTO','RESERVADA','MOMENTANEO','DEUDA') DEFAULT 'DISPONIBLE',
  `descripcion` varchar(255) DEFAULT NULL,
  `tv` tinyint(1) NOT NULL DEFAULT 0,
  `bano` tinyint(1) NOT NULL DEFAULT 0,
  `ventilador` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`habitacionID`),
  UNIQUE KEY `uk_habitacion_empresa` (`empresaID`,`numero`),
  KEY `tipohabitacionID` (`tipohabitacionID`),
  KEY `idx_estado_hab` (`estado`),
  KEY `idx_hab_emp_est` (`empresaID`,`_estado`),
  KEY `idx_hab_estado_hab` (`estado`),
  CONSTRAINT `habitaciones_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`) ON DELETE CASCADE,
  CONSTRAINT `habitaciones_ibfk_2` FOREIGN KEY (`tipohabitacionID`) REFERENCES `tipo_habitaciones` (`tipohabitacionID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
*/
-- Estructura de tabla para `hospedajes` --
CREATE TABLE `hospedajes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `hospedajeID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `checkin` datetime DEFAULT current_timestamp(),
  `checkout` datetime NOT NULL,
  `monto` decimal(15,2) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') DEFAULT 'ACTIVO',
  `habitacionID` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `cajaID` int(11) DEFAULT NULL,
  `ingresoID` int(11) NOT NULL,
  PRIMARY KEY (`hospedajeID`),
  KEY `idx_estado` (`estado`),
  KEY `idx_habitacion` (`habitacionID`),
  KEY `fk_hospedajes_ingreso` (`ingresoID`),
  KEY `idx_hosp_empresa` (`empresaID`),
  KEY `idx_hosp_habitacion` (`habitacionID`),
  KEY `idx_hosp_estado` (`_estado`),
  KEY `idx_estado_checkout` (`estado`,`checkout`),
  KEY `idx_stats_hospedajes` (`empresaID`,`_fec_insercion`),
  CONSTRAINT `fk_hospedajes_ingreso` FOREIGN KEY (`ingresoID`) REFERENCES `ingresos` (`ingresoID`),
  CONSTRAINT `hospedajes_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `hospedajes_ibfk_2` FOREIGN KEY (`habitacionID`) REFERENCES `habitaciones` (`habitacionID`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `hospedajes_clientes` --
CREATE TABLE `hospedajes_clientes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `hospedajeclienteID` int(11) NOT NULL AUTO_INCREMENT,
  `hospedajeID` int(11) NOT NULL,
  `clienteID` int(11) NOT NULL,
  `empresaID` int(11) DEFAULT NULL,
  PRIMARY KEY (`hospedajeclienteID`),
  KEY `clienteID` (`clienteID`),
  KEY `fk_empresa` (`empresaID`),
  KEY `idx_hc` (`hospedajeID`,`clienteID`),
  CONSTRAINT `fk_empresa` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `hospedajes_clientes_ibfk_1` FOREIGN KEY (`hospedajeID`) REFERENCES `hospedajes` (`hospedajeID`),
  CONSTRAINT `hospedajes_clientes_ibfk_2` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`)
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `incidentes` --
CREATE TABLE `incidentes` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `incidenteID` int(11) NOT NULL AUTO_INCREMENT,
  `clienteID` int(11) NOT NULL,
  `empresaID` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `estado` enum('PENDIENTE','ACEPTADO','RECHAZADO') DEFAULT 'PENDIENTE',
  `usuarioID` int(11) NOT NULL,
  `fecha_atencion` datetime DEFAULT NULL,
  `solucion` text DEFAULT NULL,
  PRIMARY KEY (`incidenteID`),
  KEY `clienteID` (`clienteID`),
  KEY `empresaID` (`empresaID`),
  KEY `usuarioID` (`usuarioID`),
  CONSTRAINT `incidentes_ibfk_1` FOREIGN KEY (`clienteID`) REFERENCES `clientes` (`clienteID`),
  CONSTRAINT `incidentes_ibfk_2` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `incidentes_ibfk_3` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `ingresos` --
CREATE TABLE `ingresos` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `ingresoID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `cajaID` int(11) NOT NULL,
  `cuentaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `monto_total` decimal(10,2) NOT NULL,
  `concepto` varchar(200) DEFAULT NULL,
  `entregado` tinyint(4) NOT NULL DEFAULT 0,
  `fecha_entrega` timestamp NULL DEFAULT NULL,
  `recaudacionID` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ingresoID`),
  KEY `cuentaID` (`cuentaID`),
  KEY `fk_ingreso_recaudacion` (`recaudacionID`),
  KEY `idx_caja_empresa` (`cajaID`,`empresaID`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_stats_ingresos` (`empresaID`,`fecha`),
  CONSTRAINT `fk_ingreso_recaudacion` FOREIGN KEY (`recaudacionID`) REFERENCES `recaudaciones` (`recaudacionID`),
  CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `ingresos_ibfk_2` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `ingresos_ibfk_3` FOREIGN KEY (`cuentaID`) REFERENCES `cuentas` (`cuentaID`),
  CONSTRAINT `ingresos_ibfk_4` FOREIGN KEY (`recaudacionID`) REFERENCES `recaudaciones` (`recaudacionID`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `ingreso_pagos` --
CREATE TABLE `ingreso_pagos` (
  `ingresopagoID` int(11) NOT NULL AUTO_INCREMENT,
  `ingresoID` int(11) NOT NULL,
  `formapagoID` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  PRIMARY KEY (`ingresopagoID`),
  KEY `ingresoID` (`ingresoID`),
  KEY `formapagoID` (`formapagoID`),
  CONSTRAINT `ingreso_pagos_ibfk_1` FOREIGN KEY (`ingresoID`) REFERENCES `ingresos` (`ingresoID`),
  CONSTRAINT `ingreso_pagos_ibfk_2` FOREIGN KEY (`formapagoID`) REFERENCES `formas_pago` (`formapagoID`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `migracion_log` --
CREATE TABLE `migracion_log` (
  `migracionID` int(11) NOT NULL AUTO_INCREMENT,
  `tabla_origen` varchar(50) NOT NULL,
  `tabla_destino` varchar(50) NOT NULL,
  `registros_origen` int(11) NOT NULL,
  `registros_migrados` int(11) NOT NULL,
  `fecha_migracion` datetime DEFAULT current_timestamp(),
  `estado` enum('PENDIENTE','EN_PROCESO','COMPLETADO','ERROR') DEFAULT 'PENDIENTE',
  `error_detalle` text DEFAULT NULL,
  `usuario_migracion` int(11) NOT NULL,
  PRIMARY KEY (`migracionID`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*
-- Estructura de tabla para `notificaciones` --
CREATE TABLE `notificaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL,
  `notificacionID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_completado` int(11) NOT NULL DEFAULT 0,
  `fecha_notificacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`notificacionID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `opciones` --
CREATE TABLE `opciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `opcionID` int(11) NOT NULL AUTO_INCREMENT,
  `grupoID` int(11) NOT NULL,
  `opcion` varchar(200) NOT NULL,
  `contenido` varchar(200) NOT NULL,
  `orden` int(11) NOT NULL,
  `funcionalidadID` int(11) DEFAULT NULL,
  PRIMARY KEY (`opcionID`),
  KEY `grupoID` (`grupoID`),
  CONSTRAINT `opciones_ibfk_1` FOREIGN KEY (`grupoID`) REFERENCES `grupos` (`grupoID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `paises` --
CREATE TABLE `paises` (
  `paisID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo_iso` char(2) DEFAULT NULL,
  `_estado` char(1) DEFAULT 'A',
  PRIMARY KEY (`paisID`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `planes` --
CREATE TABLE `planes` (
  `planID` int(11) NOT NULL AUTO_INCREMENT,
  `tarea` varchar(255) NOT NULL,
  `tipo` enum('CORTO','LARGO') NOT NULL,
  `estado` enum('PENDIENTE','PROCESO','TERMINADO') DEFAULT 'PENDIENTE',
  PRIMARY KEY (`planID`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `recaudaciones` --
CREATE TABLE `recaudaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `recaudacionID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `cajaID` int(11) DEFAULT NULL,
  `usuariorecepcionistaID` int(11) NOT NULL,
  `usuariopropietarioID` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `comprobante_nro` varchar(255) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`recaudacionID`),
  KEY `empresaID` (`empresaID`),
  KEY `usuariopropietarioID` (`usuariopropietarioID`),
  KEY `usuariorecepcionistaID` (`usuariorecepcionistaID`),
  KEY `fk_recaudacion_caja` (`cajaID`),
  CONSTRAINT `fk_recaudacion_caja` FOREIGN KEY (`cajaID`) REFERENCES `cajas` (`cajaID`),
  CONSTRAINT `recaudaciones_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`),
  CONSTRAINT `recaudaciones_ibfk_2` FOREIGN KEY (`usuariopropietarioID`) REFERENCES `usuarios` (`usuarioID`),
  CONSTRAINT `recaudaciones_ibfk_3` FOREIGN KEY (`usuariorecepcionistaID`) REFERENCES `usuarios` (`usuarioID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `reservas` --
CREATE TABLE `reservas` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `reservaID` int(11) NOT NULL AUTO_INCREMENT,
  `empresaID` int(11) NOT NULL,
  `checkin` datetime DEFAULT NULL,
  `checkout` datetime DEFAULT NULL,
  `precio` decimal(15,2) DEFAULT NULL,
  `estado` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`reservaID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `reserva_habitacion` --
CREATE TABLE `reserva_habitacion` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `reservahabitacionID` int(11) NOT NULL AUTO_INCREMENT,
  `reservaID` int(11) NOT NULL,
  `habitacionID` int(11) NOT NULL,
  PRIMARY KEY (`reservahabitacionID`),
  KEY `reservaID` (`reservaID`),
  KEY `habitacionID` (`habitacionID`),
  CONSTRAINT `reserva_habitacion_ibfk_1` FOREIGN KEY (`reservaID`) REFERENCES `reservas` (`reservaID`),
  CONSTRAINT `reserva_habitacion_ibfk_2` FOREIGN KEY (`habitacionID`) REFERENCES `habitaciones` (`habitacionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `roles` --
CREATE TABLE `roles` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `rolID` int(11) NOT NULL AUTO_INCREMENT,
  `rol` varchar(20) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`rolID`),
  UNIQUE KEY `rol` (`rol`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `tipo_habitaciones` --
CREATE TABLE `tipo_habitaciones` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `tipohabitacionID` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `precio` decimal(15,2) NOT NULL,
  `empresaID` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`tipohabitacionID`),
  KEY `empresaID` (`empresaID`),
  CONSTRAINT `tipo_habitaciones_ibfk_1` FOREIGN KEY (`empresaID`) REFERENCES `empresa` (`empresaID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `usuarios` --
CREATE TABLE `usuarios` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `usuarioID` int(11) NOT NULL AUTO_INCREMENT,
  `empleadoID` int(11) DEFAULT NULL,
  `usuario` varchar(15) NOT NULL,
  `clave` varchar(200) NOT NULL,
  PRIMARY KEY (`usuarioID`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `empleadoID` (`empleadoID`),
  CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`empleadoID`) REFERENCES `empleados` (`empleadoID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Estructura de tabla para `usuarios_roles` --
CREATE TABLE `usuarios_roles` (
  `_fec_insercion` timestamp NOT NULL DEFAULT current_timestamp(),
  `_fec_modificacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `_usuario` int(11) NOT NULL,
  `_estado` char(1) NOT NULL DEFAULT 'A',
  `usuariorolID` int(11) NOT NULL AUTO_INCREMENT,
  `rolID` int(11) NOT NULL,
  `usuarioID` int(11) NOT NULL,
  PRIMARY KEY (`usuariorolID`),
  KEY `rolID` (`rolID`),
  KEY `usuarioID` (`usuarioID`),
  CONSTRAINT `usuarios_roles_ibfk_1` FOREIGN KEY (`rolID`) REFERENCES `roles` (`rolID`) ON DELETE CASCADE,
  CONSTRAINT `usuarios_roles_ibfk_2` FOREIGN KEY (`usuarioID`) REFERENCES `usuarios` (`usuarioID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
*/