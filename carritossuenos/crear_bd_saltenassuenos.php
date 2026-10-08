<?php

$host = 'localhost';
$user = 'root';
$pass = '';

try {
    // 1. Conexión general para crear la base de datos
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `bd_saltenassuenos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Base de datos `bd_saltenassuenos` creada/verificada con éxito.\n";

    // 2. Conexión a bd_saltenassuenos
    $pdo = new PDO("mysql:host=$host;dbname=bd_saltenassuenos;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Tabla 1: productos
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `productos` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nombre` VARCHAR(100) NOT NULL,
        `precio` DECIMAL(10,2) NOT NULL,
        `activo` TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

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

    // Tabla 4: ventas
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `ventas` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha_hora` DATETIME NOT NULL,
        `producto_id` INT NULL,
        `promocion_id` INT NULL,
        `cantidad` INT NOT NULL DEFAULT 1,
        `monto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        FOREIGN KEY (`producto_id`) REFERENCES `productos`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`promocion_id`) REFERENCES `promociones`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Tabla 5: cierres
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `cierres` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `fecha` DATE NOT NULL,
        `total_vendidas` INT NOT NULL DEFAULT 0,
        `total_sobrantes` INT NOT NULL DEFAULT 0,
        `dinero_cobrado` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `observaciones` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Tablas creadas con éxito.\n";

    // 3. Insertar semillas si no existen
    $countProd = $pdo->query("SELECT COUNT(*) FROM `productos`")->fetchColumn();
    if ($countProd == 0) {
        $pdo->exec("
        INSERT INTO `productos` (`nombre`, `precio`) VALUES
        ('Pollo', 10.00),
        ('Carne', 10.00),
        ('Fricasé', 11.00),
        ('Veggie', 10.00);
        ");
        echo "Productos iniciales insertados.\n";
    }

    $countPromo = $pdo->query("SELECT COUNT(*) FROM `promociones`")->fetchColumn();
    if ($countPromo == 0) {
        $pdo->exec("
        INSERT INTO `promociones` (`nombre`, `unidades`, `precio`) VALUES
        ('Combo 3 Salteñas x 25 Bs', 3, 25.00),
        ('Promo 5 Salteñas x 40 Bs', 5, 40.00);
        ");
        echo "Promociones iniciales insertadas.\n";
    }

    // Insertar un stock de prueba de hoy si no hay
    $hoy = date('Y-m-d');
    $countStock = $pdo->query("SELECT COUNT(*) FROM `stock_diario` WHERE `fecha` = '$hoy'")->fetchColumn();
    if ($countStock == 0) {
        $pdo->exec("
        INSERT INTO `stock_diario` (`fecha`, `producto_id`, `cantidad_enviada`, `aceptado`) VALUES
        ('$hoy', 1, 40, 0),
        ('$hoy', 2, 30, 0),
        ('$hoy', 3, 15, 0);
        ");
        echo "Stock inicial de prueba del día insertado.\n";
    }

    echo "¡Inicialización de base de datos bd_saltenassuenos completada satisfactoriamente!\n";

} catch (PDOException $e) {
    echo "Error de Base de Datos: " . $e->getMessage() . "\n";
}
