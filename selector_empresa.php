<?php
session_start();
 // --- NUEVA LÓGICA DE LIMPIEZA ---
// Al regresar al selector, eliminamos los rastros de la empresa anterior
if (isset($_SESSION['empresaID'])) {
    unset($_SESSION['empresaID']);
    unset($_SESSION['nombre_empresa']);
    unset($_SESSION['datos_empresa']);
    unset($_SESSION['caja_abierta_id']);
}
require_once("conexion.php");

// 1. Procesar cierre de sesión (Optimizado)
if (isset($_POST['cerrar_sesion']) && $_POST['cerrar_sesion'] == '1') {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// 2. Verificar sesión activa
if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: index.php");
    exit();
}

/** * OPTIMIZACIÓN DE RENDIMIENTO: 
 * Consultamos los colores y las empresas en un solo bloque para evitar múltiples llamadas.
 */
$color_primario = '#212529'; // Gris oscuro neutro
$color_secundario = '#ffffff';

try {
    // 3. CONSULTA DE EMPRESAS
    // Opción C: El ADMINISTRADOR ve todas las empresas sin necesitar contrato laboral.
    // Para el resto de roles, se valida normalmente con empleado_empresas.
    $rol_sesion = strtoupper($_SESSION['sesion_rol'] ?? '');

    if ($rol_sesion === 'ADMINISTRADOR') {
        // ADMINISTRADOR: acceso directo a todas las empresas activas
        $sql = "SELECT DISTINCT emp.empresaID, emp.nombre, emp.color_primario, emp.color_secundario, emp.logo_agencia
                FROM empresa emp
                WHERE emp._estado <> 'X'
                ORDER BY emp.nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([]);
    } else {
        // OTROS ROLES: validación normal por contrato laboral
        $id_empleado_busqueda = $_SESSION["sesion_id_empleado"] ?? 0;
        $sql = "SELECT DISTINCT emp.empresaID, emp.nombre, emp.color_primario, emp.color_secundario, emp.logo_agencia
                FROM empresa emp
                INNER JOIN empleado_empresas ee ON emp.empresaID = ee.empresaID
                WHERE ee.empleadoID = ? 
                AND emp._estado <> 'X'
                AND ee._estado <> 'X'
                AND ee.estado_laboral = 'ACTIVO'
                ORDER BY emp.nombre";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_empleado_busqueda]);
    }

    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error_msg = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Seleccionar Empresa - Dulces Sueños</title>
    <link href='bootstrap/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background-color: #f8f9fa; min-height: 100vh; }
        .header-section { background-color: <?php echo $color_primario; ?>; color: <?php echo $color_secundario; ?>; padding: 2rem 0; margin-bottom: 2rem; }
        .user-card { background: white; border-radius: 10px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .empresa-table { background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); overflow: hidden; }
        .empresa-table .table thead th { background-color: <?php echo $color_primario; ?>; color: <?php echo $color_secundario; ?>; padding: 1rem; border: none; }
        .btn-select { background-color: <?php echo $color_primario; ?>; color: <?php echo $color_secundario; ?>; border: none; padding: 8px 20px; font-weight: 600; border-radius: 5px; }
        .no-empresas { background: white; border-radius: 10px; padding: 3rem; text-align: center; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header-section text-center'>
            <h1 class='display-4'><i class='fas fa-building me-3'></i>Seleccionar Empresa</h1>
            <p class='lead mb-0'>Elige dónde vas a trabajar hoy</p>
        </div>

        <div class='row justify-content-center'>
            <div class='col-lg-8'>
                <div class='user-card'>
                    <div class='d-flex align-items-center justify-content-between'>
                        <div>
                            <h4 class='mb-1'><?php echo htmlspecialchars($_SESSION['sesion_nom_completo'] ?? 'Usuario'); ?></h4>
                            <p class='text-muted mb-0'><i class='fas fa-user-tag me-2'></i><?php echo htmlspecialchars($_SESSION['sesion_rol'] ?? 'Sin Rol'); ?></p>
                        </div>
                        <div class='d-flex gap-2'>
                            <?php if (strtoupper($_SESSION['sesion_rol'] ?? '') === 'ADMINISTRADOR'): ?>
                                <a href='privada/sistema/index.php' class='btn btn-dark btn-sm'>
                                    <i class='fas fa-cog me-2'></i>Panel Maestro
                                </a>
                            <?php endif; ?>
                            <form method='post' action='' class='mb-0'>
                                <input type='hidden' name='cerrar_sesion' value='1'>
                                <button type='submit' class='btn btn-danger btn-sm'>
                                    <i class='fas fa-sign-out-alt me-2'></i>Salir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (isset($error_msg)): ?>
                    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
                <?php elseif (!empty($empresas)): ?>
                    <div class='empresa-table'>
                        <table class='table table-hover'>
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th class="text-end">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empresas as $empresa): ?>
                                    <tr>
                                        <td>
                                            <div class='d-flex align-items-center gap-3'>
                                                <?php 
                                                $logo = $empresa['logo_agencia'];
                                                $src = "";
                                                if ($logo && file_exists("uploads/imagen/$logo")) $src = "uploads/imagen/$logo";
                                                elseif ($logo && file_exists("../img/$logo")) $src = "../img/$logo";

                                                if ($src): ?>
                                                    <img src='<?php echo $src; ?>' style='width: 45px; height: 45px; object-fit: cover; border-radius: 8px;'>
                                                <?php else: ?>
                                                    <div style='background:<?php echo $empresa['color_primario']; ?>; color:<?php echo $empresa['color_secundario']; ?>; width:45px; height:45px; border-radius:8px; display:flex; align-items:center; justify-content:center;'>
                                                        <i class='fas fa-store'></i>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($empresa['nombre']); ?></strong>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <form method='post' action='validar1.php' class='mb-0'>
                                                <input type='hidden' name='empresaID' value='<?php echo $empresa['empresaID']; ?>'>
                                                <button type='submit' class='btn btn-select'>Ingresar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class='no-empresas'>
                        <i class='fas fa-exclamation-triangle fa-3x text-muted mb-3'></i>
                        <h4>No hay empresas disponibles</h4>
                        <p class='text-muted'>ID Empleado: <?php echo $id_empleado_busqueda; ?>. Verifica tu asignación.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>