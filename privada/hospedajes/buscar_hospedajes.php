<?php
require_once("../seguridad/seguridad_ajax.php");
header('Content-Type: application/json; charset=utf-8');

$empresaID = $_SESSION['empresaID'];

try {
    // Consulta para obtener el listado base para el buscador
    // Necesitamos traer los datos de los clientes por separado para que el JS pueda filtrar por Nombre/Apellido/CI
    $sql = "SELECT 
                u.usuario, 
                h.hospedajeID, 
                h.estado, 
                h.checkin, 
                h.checkout, 
                hab.numero AS habitacion_numero, 
                h.monto,
                c.nombres, 
                CONCAT_WS(' ', c.apellido1, c.apellido2) AS apellidos, 
                c.ci,
                (SELECT GROUP_CONCAT(CONCAT_WS(' ', c2.apellido1, c2.apellido2, c2.nombres) SEPARATOR ', ')
                 FROM hospedajes_clientes hc2 
                 JOIN clientes c2 ON hc2.clienteID = c2.clienteID 
                 WHERE hc2.hospedajeID = h.hospedajeID AND hc2._estado <> 'X') as clientes
            FROM hospedajes h
            INNER JOIN hospedajes_clientes hc ON h.hospedajeID = hc.hospedajeID
            INNER JOIN clientes c ON hc.clienteID = c.clienteID
            INNER JOIN usuarios u ON h._usuario = u.usuarioID
            INNER JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
            WHERE h._estado <> 'X' 
            AND hc._estado <> 'X'
            AND h.empresaID = ?
            ORDER BY h.hospedajeID DESC";

    $rs = $db->obtenerTodo($sql, [$empresaID]);

    // Formatear fechas para que el JS las muestre bien
    foreach ($rs as &$fila) {
        $fila['checkin'] = date('d/m/Y H:i', strtotime($fila['checkin']));
        $fila['checkout'] = date('d/m/Y H:i', strtotime($fila['checkout']));
    }

    echo json_encode($rs);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>
