<?php
// Conexión a la base de datos de Carritos
if (!function_exists('getDBConnection')) {
    function getDBConnection()
    {
        static $pdo = null;
        if ($pdo === null) {
            $host = 'sdb-65.hosting.stackcp.net';
            $dbname = 'carrito_suenos-353033395dd3';
            $user = 'carrito_suenos-353033395dd3';
            $pass = 'SCARYmovie1.';
            $charset = 'utf8mb4';

            try {
                $pdo = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset={$charset}",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
            } catch (PDOException $e) {
                // Fallback a servidor local XAMPP si el servidor remoto no responde en entorno local
                try {
                    $pdo = new PDO(
                        "mysql:host=localhost;dbname=bd_saltenassuenos;charset=utf8mb4",
                        "root",
                        "",
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        ]
                    );
                } catch (PDOException $eLocal) {
                    die("<div style='background:#fee;color:#a00;padding:20px;border-radius:10px;font-family:sans-serif;margin:20px;'><h3>Error de Conexión a la Base de Datos</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>");
                }
            }
        }
        return $pdo;
    }
}
