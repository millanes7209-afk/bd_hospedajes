<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
