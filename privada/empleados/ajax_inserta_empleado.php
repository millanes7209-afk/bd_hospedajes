<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

$nombres          = trim($_POST['nombres'] ?? '');
$apellidos        = trim($_POST['apellidos'] ?? '');
$genero           = $_POST['genero'] ?? '';
$ci               = trim($_POST['ci'] ?? '');
$telefono         = trim($_POST['telefono'] ?? '');
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

// Validación mínima
if (empty($nombres) || empty($apellidos) || empty($genero) || empty($ci)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Faltan datos obligatorios']);
    exit;
}

$usuarioLogueado = $_SESSION['sesion_id_usuario'] ?? 1;

try {
    // 1. Verificar que el CI no esté ya registrado
    $sql_check = "SELECT empleadoID FROM empleados WHERE ci = ? AND _estado <> 'X'";
    $rs_check  = $db->obtenerTodo($sql_check, [$ci]);

    if (count($rs_check) > 0) {
        echo json_encode(['status' => 'ERROR', 'message' => "Ya existe un empleado con C.I. $ci"]);
        exit;
    }

    // 2. Insertar usando el método ejecutar() de tu MiConexion (PDO)
    $sql_ins = "INSERT INTO empleados 
                (nombres, apellidos, genero, ci, telefono, fecha_nacimiento, _fec_insercion, _estado, _usuario)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 'A', ?)";
    
    $params = [
        strtoupper($nombres),
        strtoupper($apellidos),
        $genero,
        $ci,
        $telefono,
        !empty($fecha_nacimiento) ? $fecha_nacimiento : null,
        $usuarioLogueado
    ];

    $rs = $db->ejecutar($sql_ins, $params);

    if ($rs) {
        $nuevoID = $db->lastInsertId();
        echo json_encode(['status' => 'SUCCESS', 'empleadoID' => $nuevoID]);
    } else {
        echo json_encode(['status' => 'ERROR', 'message' => 'Error al ejecutar la inserción en BD']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
