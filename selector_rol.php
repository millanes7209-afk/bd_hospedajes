<?php
session_start();
if (!isset($_SESSION["sesion_roles_disponibles"])) {
    header("Location: index.php");
    exit();
}

$roles = $_SESSION["sesion_roles_disponibles"];
$usuario = $_SESSION["sesion_nom_completo"];

// Procesar selección
if (isset($_GET['rolID'])) {
    foreach ($roles as $r) {
        if ($r['rolID'] == $_GET['rolID']) {
            $_SESSION["sesion_id_rol"] = $r['rolID'];
            $_SESSION["sesion_rol"] = $r['rol'];
            header("Location: selector_empresa.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Rol - Sistema Dulces</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .selector-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .role-option {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
        }
        .role-option:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border-color: #4e73df;
        }
        .role-icon {
            font-size: 1.5rem;
            margin-right: 20px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4e73df;
            border-radius: 10px;
        }
        .role-name {
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
        }
        .welcome-text {
            font-size: 0.9rem;
            color: #a0a0a0;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="selector-card">
        <div class="mb-4">
            <i class="fas fa-user-shield fa-3x text-primary"></i>
        </div>
        <h3>¿Cómo deseas ingresar?</h3>
        <p class="welcome-text">Hola, <strong><?php echo $usuario; ?></strong>. Tienes múltiples roles asignados. Selecciona uno para continuar:</p>
        
        <div class="mt-4">
            <?php foreach ($roles as $r): ?>
                <a href="?rolID=<?php echo $r['rolID']; ?>" class="role-option">
                    <div class="role-icon">
                        <i class="fas <?php 
                            echo (stripos($r['rol'], 'admin') !== false) ? 'fa-user-tie' : 
                                 ((stripos($r['rol'], 'recep') !== false) ? 'fa-concierge-bell' : 'fa-id-badge'); 
                        ?>"></i>
                    </div>
                    <div class="role-name"><?php echo $r['rol']; ?></div>
                    <div class="ms-auto"><i class="fas fa-chevron-right opacity-50"></i></div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 pt-3 border-top border-secondary">
            <a href="index.php" class="btn btn-link text-decoration-none text-muted small">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </div>
    </div>
</body>
</html>
