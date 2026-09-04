<?php
session_start();
require_once("../../conexion.php");

$usuarioID = $_POST['usuarioID'];
$empleadoID = $_POST['empleadoID'];
$usuario = trim($_POST['usuario']);
$clave = $_POST['clave'] ?? '';
$usuarioLogueado = $_SESSION['sesion_id_usuario'];

try {
    $db->beginTransaction();

    $ahora = date('Y-m-d H:i:s');

    if (!empty($clave)) {
        // Actualizar con nueva clave
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET empleadoID = ?, usuario = ?, clave = ?, _fec_modificacion = ?, _usuario = ? WHERE usuarioID = ?";
        $db->ejecutar($sql, [$empleadoID, $usuario, $hash, $ahora, $usuarioLogueado, $usuarioID]);
    } else {
        // Actualizar sin tocar la clave
        $sql = "UPDATE usuarios SET empleadoID = ?, usuario = ?, _fec_modificacion = ?, _usuario = ? WHERE usuarioID = ?";
        $db->ejecutar($sql, [$empleadoID, $usuario, $ahora, $usuarioLogueado, $usuarioID]);
    }

    $db->commit();
    $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
    $_SESSION['mensaje_tipo'] = "success";

} catch (Exception $e) {
    if ($db->inTransaction())
        $db->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "danger";
}

header("Location: empleados.php");
exit();
?>