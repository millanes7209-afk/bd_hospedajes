<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

$ci = isset($_GET['ci']) ? trim($_GET['ci']) : '';

if (empty($ci)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'C.I. vacío']);
    exit;
}

// ── Helper: verificar si el empleado ya tiene usuario (GLOBAL, sin filtro de empresa)
function buscarUsuarioDeEmpleado($db, $empleadoID) {
    $rs = $db->obtenerTodo(
        "SELECT u.usuarioID, u.usuario FROM usuarios u
         WHERE u.empleadoID = ? AND u._estado <> 'X'",
        [$empleadoID]
    );
    return count($rs) > 0
        ? ['tiene_usuario' => true,  'Usuario' => $rs[0]]
        : ['tiene_usuario' => false, 'Usuario' => null];
}

try {
    // 1. Buscar en empleados
    $rs = $db->obtenerTodo(
        "SELECT * FROM empleados WHERE ci = ? AND _estado <> 'X'",
        [$ci]
    );

    if (count($rs) > 0) {
        $empleado  = $rs[0];
        $empresaID = $_SESSION['empresaID'];

        // Verificar usuario (global — sin filtro de empresa)
        $infoUsuario = buscarUsuarioDeEmpleado($db, $empleado['empleadoID']);

        // 2. Buscar contrato activo en ESTA empresa (JOIN roles para texto)
        $rs_c = $db->obtenerTodo(
            "SELECT ee.*, r.rol AS rol_texto
             FROM empleado_empresas ee
             INNER JOIN roles r ON ee.rolID = r.rolID
             WHERE ee.empleadoID = ?
               AND ee.empresaID  = ?
               AND ee._estado   <> 'X'
               AND ee.estado_laboral = 'ACTIVO'
             ORDER BY ee.empleadoempresaID DESC",
            [$empleado['empleadoID'], $empresaID]
        );

        if (count($rs_c) > 0) {
            // TIENE CONTRATO en esta empresa
            echo json_encode([
                'status'        => 'YA_TIENE_CONTRATO',
                'Empleado'      => $empleado,
                'Contrato'      => $rs_c[0],
                'tiene_usuario' => $infoUsuario['tiene_usuario'],
                'Usuario'       => $infoUsuario['Usuario']
            ]);
        } else {
            // EXISTE pero SIN CONTRATO en esta empresa
            echo json_encode([
                'status'        => 'EXISTE_SIN_CONTRATO',
                'Empleado'      => $empleado,
                'tiene_usuario' => $infoUsuario['tiene_usuario'],
                'Usuario'       => $infoUsuario['Usuario']
            ]);
        }
    } else {
        echo json_encode(['status' => 'NO_EXISTE']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
