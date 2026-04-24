<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

if (!isset($_SESSION["sesion_id_usuario"])) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Sesión no válida.']);
    exit;
}

$adminID = $_SESSION['sesion_id_usuario'];
$adminRol = strtoupper($_SESSION['sesion_rol'] ?? '');
$targetEmpleadoID = $_POST['empleadoID'] ?? '';

if (empty($targetEmpleadoID)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'ID de empleado no recibido.']);
    exit;
}

try {
    // 1. Obtener datos del objetivo (incluyendo su rol actual)
    $sql_target = "SELECT u.usuarioID, r.rol 
                   FROM usuarios u
                   INNER JOIN empleados e ON u.empleadoID = e.empleadoID
                   INNER JOIN empleado_empresas ee ON e.empleadoID = ee.empleadoID
                   INNER JOIN roles r ON ee.rolID = r.rolID
                   WHERE e.empleadoID = ? AND ee.estado_laboral = 'ACTIVO' AND u._estado <> 'X'";
    $target = $db->obtenerFila($sql_target, [$targetEmpleadoID]);

    if (!$target) {
        throw new Exception("El empleado no tiene un usuario activo.");
    }

    $targetRol = strtoupper($target['rol']);

    // 2. VALIDACIÓN JERÁRQUICA
    // Un Propietario NO puede resetear a otro Propietario ni al Administrador
    if ($adminRol === 'PROPIETARIO') {
        if ($targetRol === 'ADMINISTRADOR' || $targetRol === 'PROPIETARIO') {
            throw new Exception("Seguridad: No tiene permisos para resetear la clave de un rango igual o superior.");
        }
    }

    // 3. EJECUTAR RESET (Clave por defecto: 123456)
    $nuevaClave = "123456";
    $hashClave = password_hash($nuevaClave, PASSWORD_DEFAULT);

    $sql_upd = "UPDATE usuarios SET clave = ?, _fec_modificacion = NOW(), _usuario = ? WHERE usuarioID = ?";
    $db->ejecutar($sql_upd, [$hashClave, $adminID, $target['usuarioID']]);

    echo json_encode(['status' => 'SUCCESS', 'message' => 'Contraseña reseteada correctamente a 123456.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
