<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Denegado</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link href="../../css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-card { max-width: 500px; text-align: center; padding: 40px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-radius: 20px; background: white; }
        .error-icon { font-size: 80px; color: #dc3545; margin-bottom: 20px; }
        .btn-home { border-radius: 50px; padding: 10px 30px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="error-card">
        <i class="fas fa-user-shield error-icon"></i>
        <h2 class="fw-bold text-dark">ACCESO DENEGADO</h2>
        <p class="text-muted mb-4">Lo sentimos, no tienes los permisos necesarios para acceder a este módulo. Si crees que esto es un error, contacta al administrador del sistema.</p>
        <a href="../../index.php" class="btn btn-danger btn-home">
            <i class="fas fa-home me-2"></i>VOLVER AL INICIO
        </a>
    </div>
</body>
</html>
