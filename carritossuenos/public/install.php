<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<h2>🚀 Inicializador de Base de Datos — Carrito Sueños Hosting</h2>';

try {
    $pdo = getDBConnection();

    // Tabla 1: productos
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `productos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `precio` DECIMAL(10,2) NOT NULL,
        `activo` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p style='color:green;'>✅ Tabla <b>productos</b> creada/verificada.</p>";

    // Tabla 2: promociones
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `promociones` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `unidades` INT NOT NULL,
        `precio` DECIMAL(10,2) NOT NULL,
        `activo` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p style='color:green;'>✅ Tabla <b>promociones</b> creada/verificada.</p>";

    // Tabla 3: stock_diario
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `stock_diario` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `producto_id` INT NOT NULL,
        `cantidad_enviada` INT NOT NULL DEFAULT 0,
        `aceptado` TINYINT(1) NOT NULL DEFAULT 0,
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "<p style='color:green;'>✅ Tabla <b>stock_diario</b> creada/verificada.</p>";

    // Tabla 4: ventas
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `ventas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha_hora` DATETIME NOT NULL,
        `producto_id` INT NULL,
        `promocion_id` INT NULL,
        `cantidad` INT NOT NULL DEFAULT 1,
        `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `metodo_pago` ENUM('efectivo', 'qr') NOT NULL DEFAULT 'efectivo',
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`promocion_id`) REFERENCES `promociones`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Verificar si la columna metodo_pago existe (si la tabla ya fue creada anteriormente)
    try {
        $pdo->exec("ALTER TABLE `ventas` ADD COLUMN `metodo_pago` ENUM('efectivo', 'qr') NOT NULL DEFAULT 'efectivo';");
    } catch (Exception $e) {
        // Ignorar si ya existe
    }
    echo "<p style='color:green;'>✅ Tabla <b>ventas</b> creada/verificada con soporte Efectivo/QR.</p>";

    // Tabla 5: cierres
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `cierres` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `total_vendidas` INT NOT NULL DEFAULT 0,
        `total_sobrantes` INT NOT NULL DEFAULT 0,
        `dinero_cobrado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `dinero_efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `dinero_qr` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `observaciones` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    try {
        $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_efectivo` DECIMAL(10,2) NOT NULL DEFAULT 0.00;");
    } catch (Exception $e) {
    }
    try {
        $pdo->exec("ALTER TABLE `cierres` ADD COLUMN `dinero_qr` DECIMAL(10,2) NOT NULL DEFAULT 0.00;");
    } catch (Exception $e) {
    }
    echo "<p style='color:green;'>✅ Tabla <b>cierres</b> creada/verificada con soporte Efectivo/QR.</p>";

    // Insertar Semillas iniciales si no existen
    $countProd = $pdo->query("SELECT COUNT(*) FROM `productos`")->fetchColumn();
    if ($countProd == 0) {
        $pdo->exec("
        INSERT INTO `productos` (`nombre`, `precio`) VALUES
        ('Pollo', 10.00),
        ('Carne', 10.00),
        ('Fricasé', 11.00),
        ('Veggie', 10.00);
        ");
        echo "<p style='color:blue;'>ℹ️ Productos iniciales insertados.</p>";
    }

    $countPromo = $pdo->query("SELECT COUNT(*) FROM `promociones`")->fetchColumn();
    if ($countPromo == 0) {
        $pdo->exec("
        INSERT INTO `promociones` (`nombre`, `unidades`, `precio`) VALUES
        ('Combo 3 Salteñas x 25 Bs', 3, 25.00),
        ('Promo 5 Salteñas x 40 Bs', 5, 40.00);
        ");
        echo "<p style='color:blue;'>ℹ️ Promociones iniciales insertadas.</p>";
    }

    $hoy = date('Y-m-d');
    $countStock = $pdo->query("SELECT COUNT(*) FROM `stock_diario` WHERE `fecha` = '$hoy'")->fetchColumn();
    if ($countStock == 0) {
        $pdo->exec("
        INSERT INTO `stock_diario` (`fecha`, `producto_id`, `cantidad_enviada`, `aceptado`) VALUES
        ('$hoy', 1, 40, 0),
        ('$hoy', 2, 30, 0),
        ('$hoy', 3, 15, 0);
        ");
        echo "<p style='color:blue;'>ℹ️ Stock inicial de prueba del día insertado.</p>";
    }

    echo "<h3>🎉 ¡Base de datos de producción actualizada con soporte QR y Efectivo!</h3>";
    echo "<p><a href='index.php' style='padding:10px 20px; background:#22c55e; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Ir al POS Celular</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error durante la instalación: " . htmlspecialchars($e->getMessage()) . "</p>";
}
