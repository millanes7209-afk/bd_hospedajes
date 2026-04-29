<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- VERIFICACIÓN DE LICENCIA (KILL-SWITCH) ---
$archivo_lic = __DIR__ . '/.lic.key';
if (file_exists($archivo_lic)) {
    $lic_data = base64_decode(file_get_contents($archivo_lic));
    $partes = explode("|", $lic_data);
    if (count($partes) == 2 && $partes[0] === "EXPIRATION_LIMIT") {
        if (date('Y-m-d') > $partes[1]) {
            die("<div style='font-family:sans-serif; text-align:center; padding-top:100px; color:#c0392b; background:#111; height:100vh;'>
                    <h1>⚠️ ACCESO BLOQUEADO</h1>
                    <p style='font-size:18px;'>El período de prueba ha finalizado.</p>
                    <p style='color:#777;'>Contacte al proveedor del software para extender su licencia.</p>
                 </div>");
        }
    }
}
// ----------------------------------------------

// 1. VERIFICACIÓN DE SESIÓN
if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: /dulces/sis_segundo_2023/index.php");
    exit();
}

require_once(__DIR__ . "/../../conexion.php");

// 2. IDENTIFICAR EL ARCHIVO ACTUAL
// Obtenemos solo el nombre del archivo (ej: empleados.php)
$archivo_actual = basename($_SERVER['SCRIPT_NAME']);

// 3. EXCEPCIONES (Páginas que siempre son accesibles si estás logueado)
$excepciones = ['index.php', 'mi_perfil.php', 'acceso_denegado.php'];
if (in_array($archivo_actual, $excepciones)) {
    return; // Permitir acceso
}

// 4. VERIFICAR PERMISO EN BASE DE DATOS
$rolID = $_SESSION['sesion_id_rol'] ?? null;

// Si no hay rol en sesión, lo recuperamos
if (!$rolID) {
    $uID = $_SESSION['sesion_id_usuario'];
    $sql_rol = "SELECT rolID FROM usuarios_roles WHERE usuarioID = ? AND _estado <> 'X' LIMIT 1";
    $rs_rol = $db->obtenerTodo($sql_rol, [$uID]);
    $rolID = $rs_rol[0]['rolID'] ?? null;
    $_SESSION['sesion_id_rol'] = $rolID;
}

// 🌟 LÓGICA DE HERENCIA DE PERMISOS (PARÁMETRO auth)
// Si el archivo no tiene acceso directo, verificamos si viene con un "padre" autorizado
$archivo_a_validar = $archivo_actual;
if (isset($_REQUEST['auth']) && !empty($_REQUEST['auth'])) {
    $archivo_a_validar = basename($_REQUEST['auth']);
}

// Consultamos si el rol tiene acceso a la opción (ya sea el archivo actual o su padre autorizado)
$sql_acceso = "SELECT a.accesoID 
               FROM accesos a
               INNER JOIN opciones o ON a.opcionID = o.opcionID
               WHERE a.rolID = ? 
               AND o.contenido LIKE ? 
               AND a._estado <> 'X'
               AND o._estado <> 'X'";

$acceso = $db->obtenerTodo($sql_acceso, [$rolID, "%$archivo_a_validar%"]);

// 5. VEREDICTO
if (empty($acceso)) {
    // Si no tiene permiso ni directo ni por herencia, lo mandamos a la página de error
    header("Location: /dulces/sis_segundo_2023/privada/seguridad/acceso_denegado.php");
    exit();
}
?>
