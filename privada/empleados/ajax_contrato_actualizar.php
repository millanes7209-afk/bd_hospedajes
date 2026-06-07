<?php
require_once("../seguridad/seguridad_ajax.php");
header('Content-Type: application/json; charset=utf-8');

$usuarioID = $_SESSION['sesion_id_usuario'];
$empresaID = $_SESSION['empresaID'];
$empleadoID = $_POST['empleadoID'] ?? '';
$rolID = $_POST['rolID'] ?? '';
$sueldo = $_POST['sueldo'] ?? '';

if (empty($empleadoID) || empty($rolID) || empty($sueldo)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Faltan datos para el contrato.']);
    exit;
}

try {
    $ahora = date('Y-m-d H:i:s');

    // 1. Actualizar el contrato activo
    $sql = "UPDATE empleado_empresas 
            SET rolID = ?, sueldo = ?, _fec_modificacion = ?, _usuario = ?
            WHERE empleadoID = ? AND empresaID = ? AND estado_laboral = 'ACTIVO'";
    $db->ejecutar($sql, [$rolID, $sueldo, $ahora, $usuarioID, $empleadoID, $empresaID]);

    // 2. Sincronizar el ROL en la tabla usuarios_roles para que los permisos cambien
    $sql_user = "UPDATE usuarios_roles ur
                 INNER JOIN usuarios u ON ur.usuarioID = u.usuarioID
                 SET ur.rolID = ?, ur._fec_modificacion = ?, ur._usuario = ?
                 WHERE u.empleadoID = ? AND ur._estado <> 'X'";
    $db->ejecutar($sql_user, [$rolID, $ahora, $usuarioID, $empleadoID]);

    echo json_encode(['status' => 'SUCCESS', 'message' => 'Contrato y permisos actualizados correctamente.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>