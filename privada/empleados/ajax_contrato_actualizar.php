<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

if (!isset($_SESSION["sesion_id_usuario"]) || !isset($_SESSION["empresaID"])) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Sesión no válida.']);
    exit;
}

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
    // 1. Actualizar el contrato activo
    $sql = "UPDATE empleado_empresas 
            SET rolID = ?, sueldo = ?, _fec_modificacion = NOW(), _usuario = ?
            WHERE empleadoID = ? AND empresaID = ? AND estado_laboral = 'ACTIVO'";
    $db->ejecutar($sql, [$rolID, $sueldo, $usuarioID, $empleadoID, $empresaID]);

    // 2. Sincronizar el ROL en la tabla usuarios_roles para que los permisos cambien
    $sql_user = "UPDATE usuarios_roles ur
                 INNER JOIN usuarios u ON ur.usuarioID = u.usuarioID
                 SET ur.rolID = ?, ur._fec_modificacion = NOW(), ur._usuario = ?
                 WHERE u.empleadoID = ? AND ur._estado <> 'X'";
    $db->ejecutar($sql_user, [$rolID, $usuarioID, $empleadoID]);

    echo json_encode(['status' => 'SUCCESS', 'message' => 'Contrato y permisos actualizados correctamente.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
