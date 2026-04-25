<?php
require_once("../seguridad/seguridad_ajax.php");
header('Content-Type: application/json; charset=utf-8');

$empleadoID = $_POST['empleadoID'] ?? '';
$empresaID  = $_SESSION['empresaID'] ?? null;
$usuarioSesion = $_SESSION['sesion_id_usuario'] ?? null;

if (empty($empleadoID) || empty($empresaID)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Faltan datos para procesar la baja.']);
    exit;
}

try {
    $db->beginTransaction();

    // 1. Marcar el contrato como INACTIVO y registrar FECHA_FIN
    $sql_ee = "UPDATE empleado_empresas 
               SET estado_laboral = 'INACTIVO', fecha_fin = NOW(), _fec_modificacion = NOW(), _usuario = ?
               WHERE empleadoID = ? AND empresaID = ? AND _estado <> 'X'";
    $db->ejecutar($sql_ee, [$usuarioSesion, $empleadoID, $empresaID]);

    $db->commit();
    echo json_encode(['status' => 'SUCCESS', 'message' => 'Baja laboral procesada correctamente.']);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
