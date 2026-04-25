<?php
session_start();
require_once("../../conexion.php");

// Seguridad básica
if (!isset($_SESSION['sesion_id_usuario']) || !in_array($_SESSION['sesion_rol'], ['ADMINISTRADOR', 'PROPIETARIO'])) {
    echo json_encode(['status' => 'ERROR', 'message' => 'No tiene permisos para realizar esta acción.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$cajaIDs = $data['cajaIDs'] ?? [];
$usuarioPropietarioID = $_SESSION['sesion_id_usuario'];
$empresaID = $_SESSION['empresaID'];

if (empty($cajaIDs)) {
    echo json_encode(['status' => 'ERROR', 'message' => 'No se seleccionaron turnos para recaudar.']);
    exit();
}

try {
    $procesados = 0;
    foreach ($cajaIDs as $cajaID) {
        // 1. Obtener el recepcionista y el monto pendiente de esta caja
        $sql_info = "SELECT m.usuarioID as recepcionistaID, SUM(m.monto) as total_monto
                     FROM movimientos m
                     WHERE m.cajaID = ? AND m.entregado = 0 AND m._estado <> 'X'
                     GROUP BY m.usuarioID";
        $info = $db->obtenerTodo($sql_info, [$cajaID]);

        if ($info) {
            foreach ($info as $row) {
                $recepcionistaID = $row['recepcionistaID'];
                $monto = $row['total_monto'];
                $comprobante = "REC-" . date("Ymd") . "-" . $cajaID;

                // 2. Crear registro en recaudaciones
                $sql_rec = "INSERT INTO recaudaciones (empresaID, usuariorecepcionistaID, usuariopropietarioID, monto, comprobante_nro, fecha, _usuario, _estado) 
                            VALUES (?, ?, ?, ?, ?, NOW(), ?, 'A')";
                $db->ejecutar($sql_rec, [$empresaID, $recepcionistaID, $usuarioPropietarioID, $monto, $comprobante, $usuarioPropietarioID]);
                
                $recaudacionID = $db->ultimoInsertId();

                // 3. Marcar movimientos como entregados
                $sql_upd = "UPDATE movimientos SET 
                            entregado = 1, 
                            fecha_entrega = NOW(), 
                            recaudacionID = ?, 
                            _fec_modificacion = NOW(), 
                            _usuario = ?
                            WHERE cajaID = ? AND entregado = 0 AND usuarioID = ?";
                $db->ejecutar($sql_upd, [$recaudacionID, $usuarioPropietarioID, $cajaID, $recepcionistaID]);
                
                $procesados++;
            }
        }
    }

    echo json_encode(['status' => 'SUCCESS', 'message' => "Se han procesado $procesados recaudaciones con éxito."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
