<?php
session_start();
require_once("../../conexion.php");

$habitacionID = $_POST["habitacionID"];

// Obtener el número de la habitación
$sqlNumeroHabitacion = $db->Prepare("SELECT numero FROM habitaciones WHERE habitacionID = ?");
$rsNumeroHabitacion = $db->GetAll($sqlNumeroHabitacion, array($habitacionID));

if ($rsNumeroHabitacion) {
    $numeroHabitacion = $rsNumeroHabitacion[0]['numero'];
} else {
    $numeroHabitacion = "desconocido"; // Manejo en caso de error
}

// Verificar si la habitación tiene herencia en las tablas 'reservas' o 'hospedajes'
$sqlReservas = $db->Prepare("SELECT * FROM reservas WHERE habitacionID = ? AND _estado <> 'X'");
$rsReservas = $db->GetAll($sqlReservas, array($habitacionID));

$sqlHospedajes = $db->Prepare("SELECT * FROM hospedajes WHERE habitacionID = ? AND _estado <> 'X'");
$rsHospedajes = $db->GetAll($sqlHospedajes, array($habitacionID));

// Crear una lista de tablas donde se encuentra la herencia
$tablas = [];

if ($rsReservas) {
    $tablas[] = 'reservas';
}
if ($rsHospedajes) {
    $tablas[] = 'hospedajes';
}

// Verificar si tiene registros en alguna de las tablas
if (!empty($tablas)) {
    // Formatear las tablas en formato de lista
    $tablaHerencia = '<ul>';
    foreach ($tablas as $tabla) {
        $tablaHerencia .= "<li>$tabla</li>";
    }
    $tablaHerencia .= '</ul>';
    
    // Enviar mensaje de error en JSON con el número de la habitación
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar la habitación número $numeroHabitacion porque tiene registros en las siguientes tablas:" . $tablaHerencia
    ]);
} else {
    // Eliminar la habitación
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("habitaciones", $reg, "UPDATE", "habitacionID='".$habitacionID."'");

    // Enviar mensaje de éxito en JSON con el número de la habitación
    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "La habitación número $numeroHabitacion ha sido eliminada correctamente."
    ]);
}
?>
