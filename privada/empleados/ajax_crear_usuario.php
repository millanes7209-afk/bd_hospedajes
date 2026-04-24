<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

$empleadoID = $_POST['empleadoID'] ?? '';
$usuario    = trim($_POST['usuario'] ?? '');
$clave      = $_POST['clave'] ?? '';

// Validación básica
if (empty($empleadoID) || empty($usuario) || empty($clave)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Faltan datos requeridos']);
    exit;
}

$usuarioLogueado = $_SESSION['sesion_id_usuario'];
$empresaID       = $_SESSION['empresaID'];

try {
    // Verificar que el nombre de usuario no exista
    $rs_check = $db->obtenerTodo(
        "SELECT usuarioID FROM usuarios WHERE usuario = ? AND _estado <> 'X'",
        [$usuario]
    );
    if (count($rs_check) > 0) {
        echo json_encode(['status' => 'ERROR', 'message' => "El usuario '$usuario' ya existe en el sistema"]);
        exit;
    }

    // Verificar que el empleado no tenga ya un usuario
    $rs_check2 = $db->obtenerTodo(
        "SELECT usuarioID FROM usuarios WHERE empleadoID = ? AND _estado <> 'X'",
        [$empleadoID]
    );
    if (count($rs_check2) > 0) {
        echo json_encode(['status' => 'ERROR', 'message' => 'Este empleado ya tiene un usuario asignado']);
        exit;
    }

    // Obtener el rolID del contrato activo del empleado (el más reciente)
    $rs_contrato = $db->obtenerFila(
        "SELECT rolID FROM empleado_empresas 
         WHERE empleadoID = ? AND empresaID = ? AND _estado <> 'X' AND estado_laboral = 'ACTIVO'
         ORDER BY empleadoempresaID DESC LIMIT 1",
        [$empleadoID, $empresaID]
    );

    if (!$rs_contrato) {
        echo json_encode(['status' => 'ERROR', 'message' => 'No se encontró un contrato activo para este empleado']);
        exit;
    }

    $rolID = $rs_contrato['rolID'];

    // Hashear la clave
    $hash = password_hash($clave, PASSWORD_DEFAULT);

    // ── Iniciar transacción ──
    $db->beginTransaction();

    // INSERT en usuarios
    $db->ejecutar(
        "INSERT INTO usuarios (empleadoID, usuario, clave, _fec_insercion, _estado, _usuario)
         VALUES (?, ?, ?, NOW(), 'A', ?)",
        [$empleadoID, $usuario, $hash, $usuarioLogueado]
    );
    $nuevoUsuarioID = $db->lastInsertId();

    // INSERT en usuarios_roles (aquí se cierra el círculo)
    $db->ejecutar(
        "INSERT INTO usuarios_roles (usuarioID, rolID, _fec_insercion, _estado, _usuario)
         VALUES (?, ?, NOW(), 'A', ?)",
        [$nuevoUsuarioID, $rolID, $usuarioLogueado]
    );

    $db->commit();

    echo json_encode([
        'status'    => 'SUCCESS',
        'usuarioID' => $nuevoUsuarioID,
        'rolID'     => $rolID
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
