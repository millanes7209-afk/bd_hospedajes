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

    if (!empty($clave)) {
        // Actualizar con nueva clave
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET empleadoID = ?, usuario = ?, clave = ?, _fec_modificacion = NOW(), _usuario = ? WHERE usuarioID = ?";
        $db->ejecutar($sql, [$empleadoID, $usuario, $hash, $usuarioLogueado, $usuarioID]);
    } else {
        // Actualizar sin tocar la clave
        $sql = "UPDATE usuarios SET empleadoID = ?, usuario = ?, _fec_modificacion = NOW(), _usuario = ? WHERE usuarioID = ?";
        $db->ejecutar($sql, [$empleadoID, $usuario, $usuarioLogueado, $usuarioID]);
    }

    $db->commit();
    $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
    $_SESSION['mensaje_tipo'] = "success";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "danger";
}

header("Location: empleados.php");
exit();
?>
