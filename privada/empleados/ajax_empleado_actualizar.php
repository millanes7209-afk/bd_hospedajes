<?php
require_once("../seguridad/seguridad_ajax.php");
header('Content-Type: application/json; charset=utf-8');

$usuarioID = $_SESSION['sesion_id_usuario'];
$empleadoID = $_POST['empleadoID'] ?? '';
$nombres = strtoupper(trim($_POST['nombres'] ?? ''));
$apellidos = strtoupper(trim($_POST['apellidos'] ?? ''));
$ci = trim($_POST['ci'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = strtoupper(trim($_POST['direccion'] ?? ''));
$genero = $_POST['genero'] ?? '';

if (empty($empleadoID) || empty($nombres) || empty($apellidos) || empty($ci)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Faltan campos obligatorios.']);
    exit;
}

try {
    // 1. Verificar duplicidad de CI en OTROS registros
    $sql_check = "SELECT empleadoID FROM empleados WHERE ci = ? AND empleadoID <> ? AND _estado <> 'X'";
    $check = $db->obtenerFila($sql_check, [$ci, $empleadoID]);

    if ($check) {
        throw new Exception("El C.I. ingresado ya pertenece a otro empleado registrado.");
    }

    // 2. Ejecutar actualización
    $sql_upd = "UPDATE empleados 
                SET nombres = ?, apellidos = ?, ci = ?, telefono = ?, genero = ?, 
                    _fec_modificacion = ?, _usuario = ?
                WHERE empleadoID = ? AND _estado <> 'X'";

    $db->ejecutar($sql_upd, [
        $nombres,
        $apellidos,
        $ci,
        $telefono,
        $genero,
        date('Y-m-d H:i:s'),
        $usuarioID,
        $empleadoID
    ]);

    echo json_encode(['status' => 'SUCCESS', 'message' => 'Empleado actualizado correctamente.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>