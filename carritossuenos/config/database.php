<?php

// Configuración para el Servidor Hosting StackCP (carritosuenos.alloggibolivia.com)
return [
    'host' => 'sdb-65.hosting.stackcp.net',
    'dbname' => 'carrito_suenos-353033395dd3',
    'username' => 'carrito_suenos-353033395dd3',
    'password' => 'SCARYmovie1.',
    'charset' => 'utf8mb4',
];

function getDBConnection()
{
    static $pdo = null;
    if ($pdo === null) {
        $config = require __DIR__ . '/database.php';
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die(json_encode(['success' => false, 'error' => 'Error de conexión BD Hosting: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
