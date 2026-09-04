<?php
session_start();
require_once("../../conexion.php");

// Proteger acceso silencioso
if (!isset($_SESSION['sesion_id_usuario']) || !isset($_SESSION['empresaID'])) {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit();
}

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$empresaID = $_SESSION['empresaID'];
$usuarioID = $_SESSION['sesion_id_usuario'];
$ahora = date('Y-m-d H:i:s');

header('Content-Type: application/json');

if ($accion === 'listar') {
    // Buscar notas pendientes (completado = 0)
    $sql = "SELECT n.notificacionID, n.mensaje, n.usuarioID, DATE_FORMAT(n._fec_insercion, '%H:%i') as hora, DATE_FORMAT(n._fec_insercion, '%d/%m/%Y') as dia, u.usuario as autor
            FROM notificaciones n
            INNER JOIN usuarios u ON n.usuarioID = u.usuarioID
            WHERE n.empresaID = ? AND n.completado = 0 AND n._estado <> 'X'
            ORDER BY n._fec_insercion ASC";
    $notas = $db->obtenerTodo($sql, [$empresaID]);
    echo json_encode(["status" => "ok", "data" => $notas]);
} elseif ($accion === 'guardar') {
    $mensaje = trim($_POST['mensaje'] ?? '');
    if (!empty($mensaje)) {
        $sql = "INSERT INTO notificaciones (empresaID, usuarioID, mensaje, completado, usuario_completado, _fec_insercion, _usuario, _estado) 
                VALUES (?, ?, ?, 0, 0, ?, ?, 'A')";
        $db->ejecutar($sql, [$empresaID, $usuarioID, $mensaje, $ahora, $usuarioID]);
        echo json_encode(["status" => "ok", "message" => "Nota guardada"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Mensaje vacío"]);
    }
} elseif ($accion === 'completar') {
    $notificacionID = (int) ($_POST['notificacionID'] ?? 0);
    if ($notificacionID > 0) {
        $sql = "UPDATE notificaciones SET completado = 1, usuario_completado = ?, _fec_modificacion = ?, _usuario = ? WHERE notificacionID = ? AND empresaID = ?";
        $db->ejecutar($sql, [$usuarioID, $ahora, $usuarioID, $notificacionID, $empresaID]);
        echo json_encode(["status" => "ok", "message" => "Tarea completada"]);
    } else {
        echo json_encode(["status" => "error", "message" => "ID inválido"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Acción no reconocida"]);
}
?>