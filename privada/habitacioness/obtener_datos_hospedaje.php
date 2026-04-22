<?php
require_once("../../conexion.php");

// Forzar respuesta JSON limpia siempre
header('Content-Type: application/json');

try {
    $habitacionID = $_GET['habitacionID'] ?? 0;

    if (!$habitacionID) {
        echo json_encode(['error' => 'Falta habitacionID']);
        exit;
    }

    // 1. Obtener datos básicos del hospedaje
    $sql = "SELECT h.hospedajeID, hab.numero, hab.habitacionID, h.monto as monto_total
            FROM hospedajes h
            JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
            WHERE h.habitacionID = ? AND h._estado <> 'X' AND h.estado = 'ACTIVO'
            ORDER BY h.hospedajeID DESC LIMIT 1";

    $row = $db->obtenerFila($sql, [$habitacionID]);

    if ($row) {
        $hospedajeID = $row['hospedajeID'];

        // 2. Obtener lista de clientes asociados
        $sqlClientes = "SELECT c.clienteID, c.ci, c.nombres, CONCAT(c.apellido1, ' ', c.apellido2) as apellidos
                        FROM hospedajes_clientes hc
                        JOIN clientes c ON hc.clienteID = c.clienteID
                        WHERE hc.hospedajeID = ? AND hc._estado <> 'X'";
        $clientes = $db->obtenerTodo($sqlClientes, [$hospedajeID]);

        // 3. Obtener el precio base actual de la habitación
        $sqlPrecioBase = "SELECT thab.precio 
                         FROM habitaciones hab
                         JOIN tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
                         WHERE hab.habitacionID = ?";
        $precioBase = $db->obtenerFila($sqlPrecioBase, [$habitacionID]);

        echo json_encode([
            'hospedajeID'   => $hospedajeID,
            'habitacionID'  => $row['habitacionID'],
            'numero'        => $row['numero'],
            'monto_total'   => $row['monto_total'],
            'precio_base'   => $precioBase['precio'] ?? 0,
            'clientes'      => $clientes
        ]);
    } else {
        echo json_encode(['error' => 'No hay hospedaje activo para esta habitación']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
}
?>
