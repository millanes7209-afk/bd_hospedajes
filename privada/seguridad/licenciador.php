<?php
session_start();
// Solo para el super admin
if (!isset($_SESSION['sesion_rol']) || $_SESSION['sesion_rol'] !== 'ADMINISTRADOR') {
    die("Acceso denegado.");
}

$archivo_licencia = __DIR__ . '/.lic.key';
$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fecha_expiracion'])) {
    $fecha = $_POST['fecha_expiracion'];
    // Encriptación simple base64 (suficiente para que el usuario normal no entienda qué es)
    $data_secreta = base64_encode("EXPIRATION_LIMIT|" . $fecha);
    file_put_contents($archivo_licencia, $data_secreta);
    $mensaje = "¡Licencia generada/actualizada con éxito! El sistema caducará el: $fecha";
}

// Leer fecha actual si existe
$fecha_actual = "No hay licencia definida (Uso ilimitado)";
if (file_exists($archivo_licencia)) {
    $contenido = base64_decode(file_get_contents($archivo_licencia));
    $partes = explode("|", $contenido);
    if (count($partes) == 2) {
        $fecha_actual = $partes[1];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generador de Licencia Maestra</title>
    <style>
        body { font-family: Arial, sans-serif; background: #222; color: #fff; padding: 40px; }
        .box { background: #333; padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; }
        input { padding: 10px; width: 100%; box-sizing: border-box; margin-bottom: 15px; }
        button { padding: 10px 20px; background: #e74c3c; color: white; border: none; font-weight: bold; cursor: pointer; }
        button:hover { background: #c0392b; }
        .msg { color: #2ecc71; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Panel de Licencia (Kill-Switch)</h2>
        <?php if ($mensaje) echo "<div class='msg'>$mensaje</div>"; ?>
        <p>Estado actual: <b><?php echo $fecha_actual; ?></b></p>
        <hr>
        <form method="post">
            <label>Fecha de Expiración (YYYY-MM-DD):</label>
            <input type="date" name="fecha_expiracion" required>
            <button type="submit">GENERAR LLAVE DE BLOQUEO</button>
        </form>
        <p style="font-size:12px; color:#aaa; margin-top:20px;">
            Al generar esto, se creará un archivo oculto `.lic.key`. El sistema quedará totalmente bloqueado si la fecha de hoy supera la configurada.
        </p>
    </div>
</body>
</html>
