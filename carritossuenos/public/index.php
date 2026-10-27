<?php
// Habilitar reporte de errores para depuración completa
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Desactivar excepciones automáticas de MySQLi
if (function_exists('mysqli_report')) {
    mysqli_report(MYSQLI_REPORT_OFF);
}

// ---------- Configuración de conexión ----------
$host = 'sdb-65.hosting.stackcp.net';
$db   = 'carrito_suenos-353033395dd3';
$user = 'millanes7209@gmail.com';
$pass = 'SCARYmovie1.'; // contraseña original

// ---------- Variables de depuración ----------
$debugInfo = [];
$debugInfo['host'] = $host;
$debugInfo['db']   = $db;
$debugInfo['user'] = $user;
$debugInfo['extension_loaded'] = extension_loaded('pdo_mysql') ? 'yes' : 'no';
$debugInfo['php_version'] = PHP_VERSION;

$connectionError = '';

// Intentar conexión PDO (más detallada)
if (!extension_loaded('pdo_mysql')) {
    $connectionError = 'Extensión pdo_mysql no está instalada en el servidor.';
    $debugInfo['pdo_extension'] = 'missing';
} else {
    $debugInfo['pdo_extension'] = 'present';
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $debugInfo['pdo'] = 'connected';
    } catch (PDOException $e) {
        $connectionError = $e->getMessage() . ' (Código: ' . $e->getCode() . ')';
        $debugInfo['pdo'] = 'error';
        $debugInfo['pdo_exception'] = $e->getMessage();
        $debugInfo['pdo_exception_code'] = $e->getCode();
    }
}

// Si PDO falla, intentar MySQLi como fallback (seguro)
if ($connectionError && extension_loaded('mysqli')) {
    $debugInfo['mysqli_attempt'] = 'yes';
    $mysqli = @new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_error) {
        $debugInfo['mysqli_error'] = $mysqli->connect_error;
        // Mantener $connectionError con información de MySQLi
        $connectionError = 'MySQLi error: ' . $mysqli->connect_error;
    } else {
        $debugInfo['mysqli'] = 'connected';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Abrir Turno</title>
    <style>
        pre.debug {background:#f8f8f8;padding:10px;border:1px solid #ccc;}
    </style>
</head>
<body>
<?php if ($connectionError): ?>
    <script>
        alert('Error de conexión: <?php echo addslashes($connectionError); ?>');
    </script>
<?php else: ?>
    <p>Conexión exitosa a la base de datos.</p>
<?php endif; ?>
<button id="btn">Abrir Turno</button>
<script>
    document.getElementById('btn').addEventListener('click', function(){
        alert('Turno abierto');
    });
</script>
<!-- Información de depuración (visible en el código fuente) -->
<pre class="debug"><?php echo json_encode($debugInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?></pre>
</body>
</html>