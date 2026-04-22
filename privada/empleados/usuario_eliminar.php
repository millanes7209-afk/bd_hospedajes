<?php
session_start();
require_once("../../conexion.php");

$id_usuario = $_POST["id_usuario"];

// Obtener el nombre de usuario
$sqlNombreUsuario = $db->Prepare("SELECT usuario FROM usuarios WHERE id_usuario = ?");
$rsNombreUsuario = $db->GetAll($sqlNombreUsuario, array($id_usuario));

if ($rsNombreUsuario) {
    $nombreUsuario = $rsNombreUsuario[0]['usuario'];
} else {
    $nombreUsuario = "desconocido"; // Manejo en caso de error
}

// Verificar si el usuario tiene registros en la tabla 'usuarios_roles'
$sqlUsuariosRoles = $db->Prepare("SELECT * FROM usuarios_roles WHERE id_usuario = ? AND _estado <> 'X'");
$rsUsuariosRoles = $db->GetAll($sqlUsuariosRoles, array($id_usuario));

// Crear una lista de tablas donde se encuentra la herencia
$tablas = [];

if ($rsUsuariosRoles) {
    $tablas[] = 'usuarios_roles';
}

// Verificar si tiene registros en alguna de las tablas
if (!empty($tablas)) {
    // Formatear las tablas en formato de lista
    $tablaHerencia = '<ul>';
    foreach ($tablas as $tabla) {
        $tablaHerencia .= "<li>$tabla</li>";
    }
    $tablaHerencia .= '</ul>';
    
    // Enviar mensaje de error en JSON con el nombre de usuario
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar al usuario $nombreUsuario porque tiene registros en las siguientes tablas:" . $tablaHerencia
    ]);
} else {
    // Eliminar (marcar como 'X') el usuario
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("usuarios", $reg, "UPDATE", "id_usuario = '".$id_usuario."'");

    // Enviar mensaje de éxito en JSON con el nombre de usuario
    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "El usuario $nombreUsuario ha sido eliminado correctamente."
    ]);
}
?>
