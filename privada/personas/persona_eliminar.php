<?php
session_start();
require_once("../../conexion.php");

$id_persona = $_POST["id_persona"];

// Obtener el nombre de la persona
$sqlNombrePersona = $db->Prepare("SELECT CONCAT_WS(' ', ap, am, nombres) AS nombre FROM personas WHERE id_persona = ?");
$rsNombrePersona = $db->GetAll($sqlNombrePersona, array($id_persona));

if ($rsNombrePersona) {
    $nombrePersona = $rsNombrePersona[0]['nombre'];
} else {
    $nombrePersona = "desconocido"; // Manejo en caso de error
}

// Verificar si la persona tiene registros en la tabla 'usuarios'
$sqlUsuarios = $db->Prepare("SELECT * FROM usuarios WHERE id_persona = ? AND _estado <> 'X'");
$rsUsuarios = $db->GetAll($sqlUsuarios, array($id_persona));

// Crear una lista de tablas donde se encuentra la herencia
$tablas = [];

if ($rsUsuarios) {
    $tablas[] = 'usuarios';
}

// Verificar si tiene registros en alguna de las tablas
if (!empty($tablas)) {
    // Formatear las tablas en formato de lista
    $tablaHerencia = '<ul>';
    foreach ($tablas as $tabla) {
        $tablaHerencia .= "<li>$tabla</li>";
    }
    $tablaHerencia .= '</ul>';
    
    // Enviar mensaje de error en JSON con el nombre de la persona
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar al empleado $nombrePersona porque tiene registros en las siguientes tablas:" . $tablaHerencia
    ]);
} else {
    // Eliminar (marcar como 'X') la persona
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("personas", $reg, "UPDATE", "id_persona = '".$id_persona."'");

    // Enviar mensaje de éxito en JSON con el nombre de la persona
    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "El empleado $nombrePersona ha sido eliminado correctamente."
    ]);
}
?>
