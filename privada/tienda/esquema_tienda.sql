-- ESQUEMA DE BASE DE DATOS PARA TIENDA
-- Este archivo contiene todas las tablas necesarias para el sistema de tienda
-- Basado en el proyecto aloja pero adaptado para el sistema de hospedajes

-- TABLA 1: productos
-- Catálogo de productos que se venden
CREATE TABLE IF NOT EXISTS productos (
    productoID INT(11) NOT NULL AUTO_INCREMENT,
    empresaID INT(11) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    medida VARCHAR(10) DEFAULT '1',
    precio_costo DECIMAL(10,2) DEFAULT 0.00,
    precio_venta DECIMAL(10,2) NOT NULL,
    stock INT(11) DEFAULT 0,
    imagen TEXT NOT NULL,
    _estado CHAR(1) DEFAULT 'A',
    _fec_insercion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario INT(11),
    PRIMARY KEY (productoID),
    KEY idx_empresa (empresaID),
    KEY idx_usuario (_usuario),
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID),
    FOREIGN KEY (_usuario) REFERENCES usuarios(usuarioID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 2: movimientos_tienda
-- Cabecera de cada transacción (venta o compra)
CREATE TABLE IF NOT EXISTS movimientos_tienda (
    movimientoID INT(11) NOT NULL AUTO_INCREMENT,
    empresaID INT(11) NOT NULL,
    usuarioID INT(11) NOT NULL,
    tipo ENUM('VENTA','COMPRA') NOT NULL,
    monto_total DECIMAL(10,2) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _estado CHAR(1) DEFAULT 'A',
    _fec_insercion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario INT(11),
    PRIMARY KEY (movimientoID),
    KEY idx_empresa (empresaID),
    KEY idx_usuario (usuarioID),
    KEY idx_usuario_registro (_usuario),
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID),
    FOREIGN KEY (usuarioID) REFERENCES usuarios(usuarioID),
    FOREIGN KEY (_usuario) REFERENCES usuarios(usuarioID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 3: movimiento_detalles
-- Detalle de productos incluidos en cada venta/compra
CREATE TABLE IF NOT EXISTS movimiento_detalles (
    detalleID INT(11) NOT NULL AUTO_INCREMENT,
    movimientoID INT(11) NOT NULL,
    productoID INT(11) NOT NULL,
    cantidad INT(11) NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    _estado CHAR(1) DEFAULT 'A',
    PRIMARY KEY (detalleID),
    KEY idx_movimiento (movimientoID),
    KEY idx_producto (productoID),
    FOREIGN KEY (movimientoID) REFERENCES movimientos_tienda(movimientoID),
    FOREIGN KEY (productoID) REFERENCES productos(productoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 4: tienda_pagos
-- Formas de pago de cada venta (solo ventas, compras son solo efectivo)
CREATE TABLE IF NOT EXISTS tienda_pagos (
    pagoID INT(11) NOT NULL AUTO_INCREMENT,
    movimientoID INT(11) NOT NULL,
    formapagoID INT(11) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    _estado CHAR(1) DEFAULT 'A',
    PRIMARY KEY (pagoID),
    KEY idx_movimiento (movimientoID),
    KEY idx_formapago (formapagoID),
    FOREIGN KEY (movimientoID) REFERENCES movimientos_tienda(movimientoID),
    FOREIGN KEY (formapagoID) REFERENCES formas_pago(formapagoID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 5: caja_tienda
-- Saldo dinámico de la tienda (como aloja)
CREATE TABLE IF NOT EXISTS caja_tienda (
    caja_tiendaID INT(11) NOT NULL AUTO_INCREMENT,
    empresaID INT(11) NOT NULL,
    saldo_efectivo DECIMAL(10,2) DEFAULT 0,
    saldo_qr DECIMAL(10,2) DEFAULT 0,
    saldo_total DECIMAL(10,2) DEFAULT 0,
    _estado CHAR(1) DEFAULT 'A',
    _fec_insercion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    _usuario INT(11),
    PRIMARY KEY (caja_tiendaID),
    KEY idx_empresa (empresaID),
    KEY idx_usuario (_usuario),
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID),
    FOREIGN KEY (_usuario) REFERENCES usuarios(usuarioID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- TABLA 6: tienda_retiros
-- Registro de retiros de ganancias (roles ADMINISTRADOR y PROPIETARIO)
CREATE TABLE IF NOT EXISTS tienda_retiros (
    retiroID INT(11) NOT NULL AUTO_INCREMENT,
    empresaID INT(11) NOT NULL,
    usuarioID INT(11) NOT NULL,
    monto_efectivo DECIMAL(10,2) DEFAULT 0,
    monto_qr DECIMAL(10,2) DEFAULT 0,
    monto_total DECIMAL(10,2) NOT NULL,
    motivo VARCHAR(255) DEFAULT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _estado CHAR(1) DEFAULT 'A',
    _fec_insercion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (retiroID),
    KEY idx_empresa (empresaID),
    KEY idx_usuario (usuarioID),
    FOREIGN KEY (empresaID) REFERENCES empresa(empresaID),
    FOREIGN KEY (usuarioID) REFERENCES usuarios(usuarioID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
