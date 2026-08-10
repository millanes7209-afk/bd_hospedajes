<?php
session_start();
require_once("../../conexion.php");

$id_Empleado = $_POST["id_Empleado"];

// Obtener el nombre de la Empleado
$sqlNombreEmpleado = $db->Prepare("SELECT CONCAT_WS(' ', ap, am, nombres) AS nombre FROM EMPLEADOS WHERE id_Empleado = ?");
$rsNombreEmpleado = $db->GetAll($sqlNombreEmpleado, array($id_Empleado));

if ($rsNombreEmpleado) {
    $nombreEmpleado = $rsNombreEmpleado[0]['nombre'];
} else {
    $nombreEmpleado = "desconocido"; // Manejo en caso de error
}

// Verificar si la Empleado tiene registros en la tabla 'usuarios'
$sqlUsuarios = $db->Prepare("SELECT * FROM usuarios WHERE id_Empleado = ? AND _estado <> 'X'");
$rsUsuarios = $db->GetAll($sqlUsuarios, array($id_Empleado));

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
    
    // Enviar mensaje de error en JSON con el nombre de la Empleado
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar al empleado $nombreEmpleado porque tiene registros en las siguientes tablas:" . $tablaHerencia
    ]);
} else {
    // Eliminar (marcar como 'X') la Empleado
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("EMPLEADOS", $reg, "UPDATE", "id_Empleado = '".$id_Empleado."'");

    // Enviar mensaje de éxito en JSON con el nombre de la Empleado
    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "El empleado $nombreEmpleado ha sido eliminado correctamente."
    ]);
}
?>
