<?php
require_once("conexion.php");

$sql = "CREATE TABLE IF NOT EXISTS productos (
    productoID INT AUTO_INCREMENT PRIMARY KEY,
    empresaID INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    medida VARCHAR(10) DEFAULT '1',
    stock INT DEFAULT 0,
    precio_venta DECIMAL(10,2) DEFAULT 0,
    imagen VARCHAR(255) DEFAULT NULL,
    _estado CHAR(1) DEFAULT 'A',
    _fec_insercion DATETIME DEFAULT CURRENT_TIMESTAMP,
    _usuario INT,
    INDEX idx_empresa (empresaID),
    INDEX idx_estado (_estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $db->ejecutar($sql);
    echo "Tabla 'productos' creada exitosamente.";
} catch (Exception $e) {
    echo "Error al crear tabla: " . $e->getMessage();
}
