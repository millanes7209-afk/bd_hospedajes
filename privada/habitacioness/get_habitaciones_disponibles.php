<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$empresaID = $_SESSION['empresaID'] ?? 0;

if ($empresaID === 0) {
    echo json_encode(['error' => 'No session']);
    exit;
}

try {
    $sql = "SELECT hab.habitacionID, hab.numero, th.nombre, th.precio
            FROM habitaciones hab
            JOIN tipo_habitaciones th ON hab.tipohabitacionID = th.tipohabitacionID
            WHERE hab.estado = 'DISPONIBLE' 
            AND hab._estado <> 'X' 
            AND th._estado <> 'X'
            AND hab.empresaID = ?
            ORDER BY hab.numero ASC";

    $rs = $db->obtenerTodo($sql, [$empresaID]);

    $disponibles = array();

    if ($rs !== false) {
        foreach ($rs as $r) {
            $disponibles[] = array(
                'habitacionID' => $r['habitacionID'],
                'numero' => $r['numero'],
                'nombre' => $r['nombre'],
                'precio' => $r['precio']
            );
        }
    }

    echo json_encode($disponibles);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error SQL Disponibles: ' . $e->getMessage()]);
}
?>
