<?php
require_once("../../conexion.php");

// Consulta para obtener el estado actual de todas las habitaciones
$sql = $db->Prepare("SELECT h.habitacionID, h.numero, t.tipo, h.estado, t.precio 
                      FROM habitaciones h
                      JOIN tipo_habitaciones t ON h.tipohabitacionID = t.tipohabitacionID
                      WHERE h._estado <> 'X'
                      ORDER BY h.numero ASC");
$rs = $db->GetAll($sql);

// Crear un array con los datos
$habitaciones = [];
foreach ($rs as $habitacion) {
    $habitaciones[] = [
        'habitacionID' => $habitacion['habitacionID'],
        'numero' => $habitacion['numero'],
        'tipo' => $habitacion['tipo'],
        'estado' => $habitacion['estado'],
        'precio' => $habitacion['precio']
    ];
}

// Devolver los datos en formato JSON
echo json_encode($habitaciones);
?>
