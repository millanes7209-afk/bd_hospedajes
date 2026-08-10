<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once("../../conexion.php");

header('Content-Type: application/json');

$empresaID = $_SESSION['empresaID'] ?? 1;
$usuarioID = $_SESSION['sesion_id_usuario'] ?? $_SESSION['usuarioID'] ?? null;
$rol = strtoupper($_SESSION['sesion_rol'] ?? $_SESSION['rol'] ?? $_SESSION['sesion_cargo'] ?? '');

// Verificar sesión
if (!$usuarioID) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No autorizado']);
    exit;
}

// Solo ADMINISTRADOR y PROPIETARIO pueden realizar retiros
if (!in_array($rol, ['ADMINISTRADOR', 'PROPIETARIO', 'ADMIN'])) {
    echo json_encode(['status' => 'error', 'mensaje' => 'No tiene permisos para realizar retiros']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true);

    if (($datos['accion'] ?? '') === 'registrar') {
        $monto_efectivo = (float) ($datos['monto_efectivo'] ?? 0);
        $monto_qr = (float) ($datos['monto_qr'] ?? 0);
        $motivo = trim($datos['motivo'] ?? '');
        $monto_total = $monto_efectivo + $monto_qr;

        if ($monto_total <= 0) {
            echo json_encode(['status' => 'error', 'mensaje' => 'El monto a retirar debe ser mayor a 0']);
            exit;
        }

        try {
            $db->beginTransaction();

            // Verificar saldo suficiente en caja
            $sql_caja = "SELECT caja_tiendaID, saldo_efectivo, saldo_qr, saldo_total
                         FROM caja_tienda
                         WHERE empresaID = ? AND _estado = 'A'
                         ORDER BY caja_tiendaID DESC
                         LIMIT 1";
            $caja = $db->obtenerFila($sql_caja, [$empresaID]);

            if (!$caja) {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'No se encontró caja para esta empresa']);
                exit;
            }

            if ($monto_efectivo > 0 && $monto_efectivo > (float) $caja['saldo_efectivo']) {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'Saldo en efectivo insuficiente (disponible: Bs. ' . number_format($caja['saldo_efectivo'], 2) . ')']);
                exit;
            }

            if ($monto_qr > 0 && $monto_qr > (float) $caja['saldo_qr']) {
                $db->rollBack();
                echo json_encode(['status' => 'error', 'mensaje' => 'Saldo QR insuficiente (disponible: Bs. ' . number_format($caja['saldo_qr'], 2) . ')']);
                exit;
            }

            // Registrar retiro
            $sql_retiro = "INSERT INTO tienda_retiros
                               (empresaID, usuarioID, monto_efectivo, monto_qr, monto_total, motivo, _estado, _fec_insercion)
                           VALUES (?, ?, ?, ?, ?, ?, 'A', NOW())";
            $db->ejecutar($sql_retiro, [$empresaID, $usuarioID, $monto_efectivo, $monto_qr, $monto_total, $motivo]);

            // Descontar caja
            $sql_update_caja = "UPDATE caja_tienda
                                 SET saldo_efectivo = saldo_efectivo - ?,
                                     saldo_qr       = saldo_qr       - ?,
                                     saldo_total    = saldo_total    - ?,
                                     _fec_modificacion = NOW()
                                 WHERE empresaID = ? AND _estado = 'A'";
            $db->ejecutar($sql_update_caja, [$monto_efectivo, $monto_qr, $monto_total, $empresaID]);

            $db->commit();

            echo json_encode(['status' => 'ok', 'mensaje' => 'Retiro registrado exitosamente']);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(['status' => 'error', 'mensaje' => 'Error al registrar retiro: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'mensaje' => 'Acción no válida']);
    }
} else {
    echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido']);
}
