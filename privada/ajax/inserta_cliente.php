<?php
session_start();
header('Content-Type: application/json');
require_once("../conexion.php");

$ci = $_POST['ci-nuevo'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$lugar_nacimiento = $_POST['lugar_nacimiento'] ?? '';
$estado_civil = $_POST['estado_civil'] ?? '';
$profesion = $_POST['profesion'] ?? '';

$response = ['success' => false];

try {
    if ($ci && $nombres && $apellidos) {
        // Validación adicional (si se necesita)
        if (!is_numeric($ci)) {
            throw new Exception('El CI debe ser un número.');
        }

        $sql = $db->Prepare("INSERT INTO clientes (ci, nombres, apellidos, fecha_nacimiento, lugar_nacimiento, est_civil, profesion, _estado) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'A')");
        $db->Execute($sql, array($ci, $nombres, $apellidos, $fecha_nacimiento, $lugar_nacimiento, $estado_civil, $profesion));

        $response['success'] = true;
    } else {
        throw new Exception('Datos incompletos.');
    }
} catch (Exception $e) {
    $response['error'] = 'Error al registrar el cliente: ' . $e->getMessage();
}

echo json_encode($response);
?>
