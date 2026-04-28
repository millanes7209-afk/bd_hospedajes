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
        // 1. Calcular el neto pendiente (Ingresos - Egresos) de esta caja
        $resI = $db->obtenerFila("SELECT SUM(monto_total) as total FROM ingresos WHERE cajaID = ? AND entregado = 0 AND _estado <> 'X'", [$cajaID]);
        $resE = $db->obtenerFila("SELECT SUM(monto_total) as total FROM egresos  WHERE cajaID = ? AND entregado = 0 AND _estado <> 'X'", [$cajaID]);
        
        $total_i = floatval($resI['total'] ?? 0);
        $total_e = floatval($resE['total'] ?? 0);
        $monto_neto = $total_i - $total_e;

        if ($monto_neto != 0 || $total_i > 0 || $total_e > 0) {
            // Obtenemos el usuario responsable de la caja para la recaudación
            $caja_info = $db->obtenerFila("SELECT usuarioID FROM cajas WHERE cajaID = ?", [$cajaID]);
            $recepcionistaID = $caja_info['usuarioID'];
            $comprobante = "REC-" . date("Ymd") . "-" . $cajaID;

            // 2. Crear registro en recaudaciones
            $sql_rec = "INSERT INTO recaudaciones (empresaID, usuariorecepcionistaID, usuariopropietarioID, monto, comprobante_nro, fecha, _usuario, _estado) 
                        VALUES (?, ?, ?, ?, ?, NOW(), ?, 'A')";
            $db->ejecutar($sql_rec, [$empresaID, $recepcionistaID, $usuarioPropietarioID, $monto_neto, $comprobante, $usuarioPropietarioID]);
            
            $recaudacionID = $db->ultimoInsertId();

            // 3. Marcar INGRESOS como entregados
            $sql_upd_i = "UPDATE ingresos SET 
                          entregado = 1, 
                          fecha_entrega = NOW(), 
                          recaudacionID = ?, 
                          _fec_modificacion = NOW(), 
                          _usuario = ?
                          WHERE cajaID = ? AND entregado = 0";
            $db->ejecutar($sql_upd_i, [$recaudacionID, $usuarioPropietarioID, $cajaID]);

            // 4. Marcar EGRESOS como entregados
            $sql_upd_e = "UPDATE egresos SET 
                          entregado = 1, 
                          fecha_entrega = NOW(), 
                          recaudacionID = ?, 
                          _fec_modificacion = NOW(), 
                          _usuario = ?
                          WHERE cajaID = ? AND entregado = 0";
            $db->ejecutar($sql_upd_e, [$recaudacionID, $usuarioPropietarioID, $cajaID]);
            
            $procesados++;
        }
    }

    echo json_encode(['status' => 'SUCCESS', 'message' => "Se han procesado $procesados recaudaciones con éxito."]);

} catch (Exception $e) {
    echo json_encode(['status' => 'ERROR', 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>
