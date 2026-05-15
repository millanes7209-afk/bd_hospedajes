<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once("../../conexion.php");

// Seguridad: Solo ADMINISTRADOR GLOBAL
if (!isset($_SESSION['sesion_id_usuario']) || !($_SESSION['sesion_es_admin'] ?? false)) {
    header("Location: ../../index.php");
    exit();
}

// Estilos mínimos para que la página se vea bien sin el menú lateral
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="../../bootstrap/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .navbar-sistema { background-color: #000; color: #fff; padding: 5px 0; border-bottom: 2px solid #007bff; }
        .nav-master { display: flex; list-style: none; padding: 0; margin: 0; }
        .nav-master li a { 
            color: #aaa; 
            text-decoration: none; 
            padding: 15px 20px; 
            display: block; 
            font-weight: 500;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        .nav-master li a:hover { color: #fff; background: rgba(255,255,255,0.1); }
        .nav-master li a.active { 
            color: #fff; 
            border-bottom-color: #007bff;
            background: rgba(0,123,255,0.1);
        }
        .btn-salir-master { background: #dc3545; color: #fff !important; border-radius: 5px; margin-left: 10px; }
        .btn-salir-master:hover { background: #c82333 !important; }
        .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); margin-top: 20px; }
    </style>
</head>
<body>
<?php
    $actual = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar-sistema">
    <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
        <ul class="nav-master">
            <li><a href="sucursales.php" class="<?php echo $actual == 'sucursales.php' ? 'active' : ''; ?>"><i class="fas fa-city me-2"></i>SUCURSALES</a></li>
            <li><a href="funcionalidades.php" class="<?php echo $actual == 'funcionalidades.php' ? 'active' : ''; ?>"><i class="fas fa-cubes me-2"></i>MÓDULOS</a></li>
            <li><a href="grupos.php" class="<?php echo $actual == 'grupos.php' ? 'active' : ''; ?>"><i class="fas fa-layer-group me-2"></i>GRUPOS</a></li>
            <li><a href="opciones.php" class="<?php echo $actual == 'opciones.php' ? 'active' : ''; ?>"><i class="fas fa-list-ul me-2"></i>OPCIONES</a></li>
            <li><a href="accesos.php" class="<?php echo $actual == 'accesos.php' ? 'active' : ''; ?>"><i class="fas fa-key me-2"></i>PERMISOS</a></li>
        </ul>
        <a href="../../selector_empresa.php" class="nav-link btn-salir-master px-3 py-2">
            <i class="fas fa-times-circle me-1"></i>SALIR
        </a>
    </div>
</nav>
<div class="container-fluid px-4 pb-5">
