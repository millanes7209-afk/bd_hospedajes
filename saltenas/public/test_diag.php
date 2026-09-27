<?php
header('Content-Type: text/plain; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DIAGNOSTICO SALTENAS.ALLOGGIBOLIVIA.COM ===\n\n";

// 1. Verificar existencia de .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo "[OK] Archivo .env ENCONTRADO.\n";
} else {
    echo "[ERROR CRITICO] Archivo .env NO EXISTE en el servidor (" . realpath(__DIR__ . '/..') . ").\n";
    echo "       SOLUCION: Copie .env.production a .env en la carpeta root del servidor.\n\n";
}

// 2. Probar bootstrap de Laravel y capturar el error exacto
echo "\n--- Probando Bootstrap de Laravel ---\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "[OK] Vendor y Bootstrap cargados correctamente.\n";

    echo "\n--- Probando conexión a la Base de Datos ---\n";
    $pdo = DB::connection()->getPdo();
    echo "[OK] Conexión PDO a MariaDB/MySQL exitosa.\n";

    // Probar consulta a tabla users
    $userCount = DB::table('users')->count();
    echo "[OK] Consulta a la tabla 'users' exitosa. Total usuarios: " . $userCount . "\n";

    // Probar consulta a carritos
    $carritos = DB::table('carritos')->count();
    echo "[OK] Consulta a la tabla 'carritos' exitosa. Total carritos: " . $carritos . "\n";

} catch (Throwable $e) {
    echo "[EXCEPCION CAPTURADA] Class: " . get_class($e) . "\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
}
