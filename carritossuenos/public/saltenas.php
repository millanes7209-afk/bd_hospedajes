<?php
// Habilitar reporting para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Datos de conexión a la base Saltenas (según .env)
$host = 'shareddb-g.hosting.stackcp.net';
$db   = 'saltenas-3731dbba';
$user = 'saltenas-3731dbba';
$pass = 'SCARYmovie1.'; // contraseña original

$connectionError = '';

if (!extension_loaded('pdo_mysql')) {
    $connectionError = 'Extensión pdo_mysql no está instalada en el servidor.';
} else {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        $connectionError = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Abrir Turno (Saltenas)</title>
</head>
<body>
<?php if ($connectionError): ?>
    <script>
        alert('Error de conexión a Saltenas: <?php echo addslashes($connectionError); ?>');
    </script>
<?php else: ?>
    <p>Conexión exitosa a la base Saltenas.</p>
<?php endif; ?>
<button id="btn">Abrir Turno</button>
<script>
    document.getElementById('btn').addEventListener('click', function(){
        alert('Turno abierto (Saltenas)');
    });
</script>
</body>
</html>