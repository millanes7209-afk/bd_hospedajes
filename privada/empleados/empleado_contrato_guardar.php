<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: text/plain; charset=utf-8');

$empresaID  = $_SESSION['empresaID']          ?? null;
$usuarioID  = $_SESSION['sesion_id_usuario']  ?? null;

// Recibir datos
$empleadoID   = $_POST['empleadoID']    ?? '';
$rolID        = $_POST['rolID']         ?? '';      // FK numérica
$sueldo       = $_POST['sueldo']        ?? '';
$fecha_inicio = $_POST['fecha_inicio']  ?? '';
$fecha_fin    = !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null;
$descripcion  = $_POST['descripcion']   ?? '';
$es_titular   = (isset($_POST['es_titular']) && $_POST['es_titular'] == '1') ? 1 : 0;

// Validar datos mínimos
if (empty($empleadoID) || empty($rolID) || empty($sueldo) || empty($fecha_inicio)) {
    exit("ERROR: Faltan datos requeridos");
}

// 1. Verificar que el rolID exista en la tabla roles
$rs_rol = $db->obtenerFila("SELECT rolID, rol FROM roles WHERE rolID = ? AND _estado <> 'X'", [$rolID]);
if (!$rs_rol) {
    exit("ERROR: El rolID=$rolID no existe en la tabla roles");
}

// 2. VALIDACIÓN: ¿Ya tiene un contrato ACTIVO con este mismo CARGO en esta empresa?
$sql_dup = "SELECT 1 FROM empleado_empresas 
            WHERE empleadoID = ? AND empresaID = ? AND rolID = ? AND estado_laboral = 'ACTIVO' AND _estado <> 'X'";
$rs_dup = $db->obtenerFila($sql_dup, [$empleadoID, $empresaID, $rolID]);

if ($rs_dup) {
    exit("ERROR: El empleado ya tiene un contrato ACTIVO como '" . $rs_rol['rol'] . "' en esta empresa. Debe finalizar el anterior antes de crear uno nuevo igual.");
}

try {
    $db->beginTransaction();

    // 1. Si es TITULAR, desactivar contratos previos de este empleado en esta empresa
    if ($es_titular === 1) {
        $db->ejecutar(
            "UPDATE empleado_empresas 
             SET estado_laboral = 'INACTIVO', _fec_modificacion = NOW()
             WHERE empleadoID = ? AND empresaID = ? AND estado_laboral = 'ACTIVO' AND _estado <> 'X'",
            [$empleadoID, $empresaID]
        );
    }

    // 2. INSERT contrato en empleado_empresas
    $sql = "INSERT INTO empleado_empresas 
                (empleadoID, rolID, empresaID, sueldo, fecha_inicio, fecha_fin,
                 estado_laboral, es_titular, _fec_insercion, _usuario, _estado)
            VALUES 
                (?, ?, ?, ?, ?, ?,
                 'ACTIVO', ?, NOW(), ?, 'A')";

    $db->ejecutar($sql, [
        $empleadoID,
        $rolID,
        $empresaID,
        $sueldo,
        $fecha_inicio,
        $fecha_fin,
        $es_titular,
        $usuarioID
    ]);

    // 2. Verificar si el empleado ya tiene usuario en el sistema (GLOBAL, sin filtro empresa)
    $rs_usuario = $db->obtenerFila(
        "SELECT usuarioID FROM usuarios WHERE empleadoID = ? AND _estado <> 'X'",
        [$empleadoID]
    );

    if ($rs_usuario) {
        // El empleado ya tiene usuario → verificar si ya tiene este rolID en usuarios_roles
        $nuevoUsuarioID = $rs_usuario['usuarioID'];

        $rs_ur = $db->obtenerFila(
            "SELECT 1 FROM usuarios_roles 
             WHERE usuarioID = ? AND rolID = ? AND _estado <> 'X'",
            [$nuevoUsuarioID, $rolID]
        );

        if (!$rs_ur) {
            // No tiene este rol → insertarlo
            $db->ejecutar(
                "INSERT INTO usuarios_roles (usuarioID, rolID, _fec_insercion, _estado, _usuario)
                 VALUES (?, ?, NOW(), 'A', ?)",
                [$nuevoUsuarioID, $rolID, $usuarioID]
            );
        }
        // Si ya tenía el rol, no hacer nada (evitar duplicados)
    }
    // Si NO tiene usuario: usuarios_roles se insertará cuando se cree el usuario (ajax_crear_usuario.php)

    $db->commit();
    echo "SUCCESS";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "ERROR: " . $e->getMessage();
}
?>
