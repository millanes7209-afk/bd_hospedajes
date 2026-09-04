<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'];

// Consulta para obtener el estado actual de todas las habitaciones de la empresa
$sql = "SELECT h.habitacionID, h.numero, t.nombre as tipo, h.estado, t.precio 
        FROM habitaciones h
        JOIN tipo_habitaciones t ON h.tipohabitacionID = t.tipohabitacionID
        WHERE h._estado <> 'X' AND h.empresaID = ?
        ORDER BY h.numero ASC";

$rs = $db->obtenerTodo($sql, [$empresaID]);

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

echo json_encode($habitaciones);
?>
