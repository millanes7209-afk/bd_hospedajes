<?php
session_start();
require_once("conexion.php");

// 1. Verificar sesión básica y empresa seleccionada
if (!isset($_SESSION["sesion_id_usuario"]) || !isset($_SESSION['empresaID'])) {
    header("Location: selector_empresa.php");
    exit();
}

$empleadoID = $_SESSION["sesion_id_empleado"] ?? 0;
$empresaID = $_SESSION['empresaID'];
$usuarioID = $_SESSION["sesion_id_usuario"];
$usuarioNombre = $_SESSION["sesion_nom_completo"];

// 2. OBTENER TODOS LOS ROLES POSIBLES
// A. Roles por contrato en esta empresa
$sql_roles_cont = "SELECT r.rolID, r.rol 
                  FROM empleado_empresas ee 
                  INNER JOIN roles r ON ee.rolID = r.rolID 
                  WHERE ee.empleadoID = ? AND ee.empresaID = ? 
                  AND ee.estado_laboral = 'ACTIVO' AND ee._estado <> 'X'";
$roles_cont = $db->obtenerTodo($sql_roles_cont, [$empleadoID, $empresaID]);

// B. Roles globales del usuario (como ADMINISTRADOR)
$sql_roles_glob = "SELECT r.rolID, r.rol 
                  FROM usuarios_roles ur 
                  INNER JOIN roles r ON ur.rolID = r.rolID 
                  WHERE ur.usuarioID = ? AND ur._estado <> 'X'";
$roles_glob = $db->obtenerTodo($sql_roles_glob, [$usuarioID]);

// Combinamos ambos evitando duplicados
$roles = array_map("unserialize", array_unique(array_map("serialize", array_merge($roles_cont, $roles_glob))));

// 3. PROCESAR SELECCIÓN
if (isset($_GET['rolID'])) {
    $rolID_sel = $_GET['rolID'];
    foreach ($roles as $r) {
        if ($r['rolID'] == $rolID_sel) {
            $_SESSION["sesion_id_rol"] = $r['rolID'];
            $_SESSION["sesion_rol"] = $r['rol'];
            header("Location: privada/habitacioness/habitaciones.php");
            exit();
        }
    }
}

// 4. AUTO-SELECCIÓN (Solo si es la PRIMERA VEZ que entra tras elegir empresa)
// Si viene de 'validar1.php' y solo hay un rol, entramos directo.
// Pero si viene de un click manual (Cambiar Rol), mostramos el selector.
if (!isset($_GET['manual']) && count($roles) == 1) {
    $_SESSION["sesion_id_rol"] = $roles[0]['rolID'];
    $_SESSION["sesion_rol"] = $roles[0]['rol'];
    header("Location: privada/habitacioness/habitaciones.php");
    exit();
}

// Si no hay roles, error
if (empty($roles)) {
    $_SESSION['mensaje'] = array('tipo' => 'danger', 'texto' => 'No tienes cargos asignados.');
    header("Location: selector_empresa.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Cargo - Dulces Sueños</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .selector-card { background: white; border-radius: 16px; padding: 40px; width: 100%; max-width: 450px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; }
        .company-badge { background: #e7f0ff; color: #0d6efd; padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; display: inline-block; margin-bottom: 20px; }
        .role-option { background: #ffffff; border: 2px solid #f0f2f5; border-radius: 12px; padding: 15px 20px; margin-bottom: 12px; cursor: pointer; transition: all 0.2s ease; display: flex; align-items: center; text-decoration: none; color: #333; }
        .role-option:hover { border-color: #0d6efd; background: #f8fbff; transform: translateY(-2px); }
        .role-icon { font-size: 1.2rem; margin-right: 15px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #f0f2f5; border-radius: 10px; color: #0d6efd; }
        .role-name { font-weight: 600; font-size: 1rem; }
    </style>
</head>
<body>
    <div class="selector-card">
        <div class="company-badge text-uppercase">
            <i class="fas fa-hotel me-1"></i> <?php echo $_SESSION['nombre_empresa'] ?? 'Empresa'; ?>
        </div>
        <h3 class="fw-bold mb-1">¿Qué cargo ocuparás?</h3>
        <p class="text-muted mb-4 small">Selecciona tu función para continuar:</p>
        
        <div class="mt-4">
            <?php foreach ($roles as $r): ?>
                <a href="?rolID=<?php echo $r['rolID']; ?>" class="role-option">
                    <div class="role-icon">
                        <i class="fas <?php 
                            echo (stripos($r['rol'], 'admin') !== false) ? 'fa-user-tie' : 
                                 ((stripos($r['rol'], 'recep') !== false) ? 'fa-concierge-bell' : 'fa-id-badge'); 
                        ?>"></i>
                    </div>
                    <div class="role-name text-uppercase"><?php echo $r['rol']; ?></div>
                    <div class="ms-auto text-muted"><i class="fas fa-chevron-right small"></i></div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="mt-4 pt-3 border-top">
            <a href="selector_empresa.php" class="btn btn-link text-decoration-none text-muted small">
                <i class="fas fa-arrow-left"></i> Cambiar de empresa
            </a>
        </div>
    </div>
</body>
</html>
