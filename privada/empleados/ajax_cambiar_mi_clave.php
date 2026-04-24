<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once("../../conexion.php");

if (!isset($_SESSION["sesion_id_usuario"])) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Sesión no válida.']);
    exit;
}

$usuarioID = $_SESSION['sesion_id_usuario'];
$nuevaClave = $_POST['nueva_clave'] ?? '';

if (strlen($nuevaClave) < 4) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Contraseña demasiado corta.']);
    exit;
}

try {
    $hashClave = password_hash($nuevaClave, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios 
            SET clave = ?, _fec_modificacion = NOW(), _usuario = ?
            WHERE usuarioID = ? AND _estado <> 'X'";
    
    $db->ejecutar($sql, [$hashClave, $usuarioID, $usuarioID]);

    echo json_encode(['status' => 'SUCCESS', 'message' => 'Contraseña actualizada.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
