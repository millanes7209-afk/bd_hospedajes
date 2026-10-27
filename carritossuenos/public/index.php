<?php
// Datos de conexión
$host = 'sdb-65.hosting.stackcp.net';
$db   = 'carrito_suenos-353033395dd3';
$user = 'millanes7209@gmail.com';
$pass = 'SCARYmovie1.';

// Intentar conectar
$mysqli = @new mysqli($host, $user, $pass, $db);
$connectionError = $mysqli->connect_error;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Abrir Turno</title>
</head>
<body>
<?php if ($connectionError): ?>
<script>alert('Error de conexión: <?php echo addslashes($connectionError); ?>');</script>
<?php else: ?>
<p>Conexión exitosa a la base de datos.</p>
<?php endif; ?>
<button id="btn">Abrir Turno</button>
<script>
document.getElementById("btn").addEventListener("click",function(){
    alert("Turno abierto");
});
</script>
</body>
</html>
