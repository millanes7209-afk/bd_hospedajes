<?php
session_start();
require_once("../../conexion.php");

$usuarioID = $_POST["usuarioID"] ?? $_POST["id_usuario"] ?? null;
$usuarioLogueado = $_SESSION["sesion_id_usuario"];

if (!$usuarioID) {
    echo json_encode(['tipo' => 'danger', 'mensaje' => 'ID de usuario no proporcionado.']);
    exit;
}

try {
    // 1. Obtener el nombre del usuario para el mensaje
    $sql_u = "SELECT usuario FROM usuarios WHERE usuarioID = ?";
    $user = $db->obtenerFila($sql_u, [$usuarioID]);
    $nombre = $user ? $user['usuario'] : "Desconocido";

    $ahora = date('Y-m-d H:i:s');

    // 2. Borrado Lógico del Usuario
    $sql_del_u = "UPDATE usuarios SET _estado = 'X', _fec_modificacion = ?, _usuario = ? WHERE usuarioID = ?";
    $db->ejecutar($sql_del_u, [$ahora, $usuarioLogueado, $usuarioID]);

    // 3. Borrado Lógico de sus Roles (Cascada lógica)
    $sql_del_r = "UPDATE usuarios_roles SET _estado = 'X', _fec_modificacion = ?, _usuario = ? WHERE usuarioID = ?";
    $db->ejecutar($sql_del_r, [$ahora, $usuarioLogueado, $usuarioID]);

    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "El usuario $nombre ha sido eliminado correctamente del sistema."
    ]);

} catch (Exception $e) {
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "Error al eliminar: " . $e->getMessage()
    ]);
}
?>