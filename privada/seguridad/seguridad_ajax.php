<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. VERIFICACIÓN DE SESIÓN BÁSICA
if (!isset($_SESSION["sesion_id_usuario"])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'message' => 'Sesión expirada o no válida.']);
    exit();
}

require_once(__DIR__ . "/../../conexion.php");

// 2. IDENTIFICAR EL ARCHIVO ACTUAL
$archivo_actual = basename($_SERVER['SCRIPT_NAME']);

// 3. LÓGICA DE HERENCIA DE PERMISOS (PARÁMETRO auth)
$rolID = $_SESSION['sesion_id_rol'] ?? null;

// Si viene un "padre" autorizado, validamos contra él. 
// Si no, validamos contra el archivo AJAX directamente.
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

if (empty($acceso)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ERROR', 'message' => 'No tienes permisos para realizar esta operación (Acceso Denegado).']);
    exit();
}
?>
