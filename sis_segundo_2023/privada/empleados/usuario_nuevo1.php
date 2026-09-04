<?php
session_start();
require_once("../../conexion.php");

$empleadoID = $_POST['empleadoID'];
$usuario = trim($_POST['usuario']);
$clave = $_POST['clave'];
$usuarioLogueado = $_SESSION['sesion_id_usuario'];
$empresaID = $_SESSION['empresaID'];

try {
    $db->beginTransaction();

    // 1. Obtener el rolID del contrato activo del empleado
    $sql_rol = "SELECT rolID FROM empleado_empresas 
                WHERE empleadoID = ? AND empresaID = ? AND _estado <> 'X' AND estado_laboral = 'ACTIVO'
                ORDER BY empleadoempresaID DESC LIMIT 1";
    $contrato = $db->obtenerFila($sql_rol, [$empleadoID, $empresaID]);

    if (!$contrato) {
        throw new Exception("El empleado no tiene un contrato activo en esta empresa.");
    }

    $rolID = $contrato['rolID'];

    // 2. Insertar el usuario
    $ahora = date('Y-m-d H:i:s');
    $hash = password_hash($clave, PASSWORD_DEFAULT);
    $sql_user = "INSERT INTO usuarios (empleadoID, usuario, clave, _fec_insercion, _usuario, _estado) 
                 VALUES (?, ?, ?, ?, ?, 'A')";
    $db->ejecutar($sql_user, [$empleadoID, $usuario, $hash, $ahora, $usuarioLogueado]);
    $nuevoUsuarioID = $db->ultimoInsertId();

    // 3. Vincular con el Rol
    $sql_rol_vinc = "INSERT INTO usuarios_roles (usuarioID, rolID, _fec_insercion, _usuario, _estado) 
                     VALUES (?, ?, ?, ?, 'A')";
    $db->ejecutar($sql_rol_vinc, [$nuevoUsuarioID, $rolID, $ahora, $usuarioLogueado]);

    $db->commit();
    $_SESSION['mensaje'] = "Usuario creado correctamente.";
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