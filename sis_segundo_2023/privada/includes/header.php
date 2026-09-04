<?php
/**
 * Header común para todas las páginas
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';

secureSessionStart();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../../css/style.css" rel="stylesheet">
    
    <style>
        .navbar-brand {
            font-weight: bold;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .btn-accion {
            margin-right: 5px;
        }
        .table th {
            background-color: #f8f9fa;
            border-top: none;
        }
        .modal {
            z-index: 1050;
        }
        .required {
            color: #dc3545;
        }
        .form-container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../../index.php">
                <i class="fas fa-home me-2"></i><?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../hospedajes/hospedajes.php">
                                <i class="fas fa-bed me-1"></i> Hospedajes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../habitaciones/habitaciones.php">
                                <i class="fas fa-door-open me-1"></i> Habitaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../clientes/clientes.php">
                                <i class="fas fa-users me-1"></i> Clientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../reportes/reportes.php">
                                <i class="fas fa-chart-bar me-1"></i> Reportes
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isAuthenticated()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i> 
                                <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Usuario'; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-key me-2"></i> Cambiar Contraseña</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../../index.php">
                                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container-fluid py-4">
        <?php
        // Mostrar mensajes de sesión si existen
        if (isset($_SESSION['flash_message'])) {
            echo showAlert($_SESSION['flash_message']['message'], $_SESSION['flash_message']['type']);
            unset($_SESSION['flash_message']);
        }
        ?>
?>
