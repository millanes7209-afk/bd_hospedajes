<?php
require_once("../seguridad/seguridad_ajax.php");
header('Content-Type: application/json; charset=utf-8');

$empresaID = $_SESSION['empresaID'];
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';

try {
    $params = [$empresaID];
    $where = "WHERE h._estado <> 'X' AND hc._estado <> 'X' AND h.empresaID = ?";

    if (!empty($nombre)) {
        $where .= " AND c.nombres LIKE ?";
        $params[] = "%$nombre%";
    }
    if (!empty($apellido)) {
        $where .= " AND (c.apellido1 LIKE ? OR c.apellido2 LIKE ?)";
        $params[] = "%$apellido%";
        $params[] = "%$apellido%";
    }
    if (!empty($ci)) {
        $where .= " AND c.ci LIKE ?";
        $params[] = "%$ci%";
    }

    // Limitamos a 100 resultados para no colapsar el navegador si la búsqueda es muy genérica
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
            $where
            ORDER BY h.hospedajeID DESC
            LIMIT 100";

    $rs = $db->obtenerTodo($sql, $params);

    foreach ($rs as &$fila) {
        $fila['checkin'] = date('d/m/Y H:i', strtotime($fila['checkin']));
        $fila['checkout'] = date('d/m/Y H:i', strtotime($fila['checkout']));
    }

    echo json_encode($rs);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
}
?>