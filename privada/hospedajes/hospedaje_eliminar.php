<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE ELIMINACIÓN SEGURA (SOFT DELETE) DE HOSPEDAJE
 * Con Auditoría Centralizada y soporte Multi-empresa.
 */

$hospedajeID = $_POST['hospedajeID'] ?? null;
$motivo = $_POST['motivo'] ?? 'Motivo no especificado';
$usuarioID = $_SESSION["sesion_id_usuario"] ?? null;
$empresaID = $_SESSION["empresaID"] ?? null;
$ahora = date("Y-m-d H:i:s");

if (!$hospedajeID || !$usuarioID || !$empresaID) {
    die("Error: Sesión expirada o datos insuficientes.");
}

try {
    if (!$db->beginTransaction()) {
        throw new Exception("No se pudo iniciar la transacción.");
    }

    // 1. Obtener datos previos para la auditoría (Monto, Habitación y Pagos)
    $sqlHosp = "SELECT habitacionID, monto FROM hospedajes WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    $hospedaje = $db->obtenerFila($sqlHosp, [$hospedajeID, $empresaID]);

    if (!$hospedaje) {
        throw new Exception("Hospedaje no encontrado o ya eliminado.");
    }

    $habitacionID = $hospedaje['habitacionID'];
    $montoAnterior = $hospedaje['monto'];

    // Obtener desglose de pagos para auditoría
    $sqlPagos = "SELECT fp.tipo, m.monto 
                 FROM movimientos m 
                 INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID 
                 WHERE m.referenciaID = ? AND m.categoria = 'HOSPEDAJE' AND m._estado = 'A'";
    $pagosOriginales = $db->obtenerTodo($sqlPagos, [$hospedajeID]);
    $detalleOriginal = json_encode($pagosOriginales);

    // 2. Registrar en la Tabla de Auditoría (Antes de borrar)
    $sqlAudit = "INSERT INTO hospedajes_auditoria_montos 
                 (hospedajeID, tipo_auditoria, monto_anterior, monto_nuevo, detalle_original, detalle_nuevo, motivo, usuarioID, fecha, empresaID) 
                 VALUES (?, 'ELIMINACION', ?, 0, ?, 'CANCELADO', ?, ?, ?, ?)";
    
    if ($db->ejecutar($sqlAudit, [$hospedajeID, $montoAnterior, $detalleOriginal, $motivo, $usuarioID, $ahora, $empresaID]) === false) {
        throw new Exception("No se pudo registrar la auditoría de eliminación.");
    }

    // 3. Marcar Hospedaje como eliminado (_estado = 'X')
    // Nota: Como pidió el usuario, NO modificamos el campo observaciones aquí.
    $sqlDel = "UPDATE hospedajes SET _estado = 'X', _fec_modificacion = ? WHERE hospedajeID = ? AND empresaID = ?";
    if ($db->ejecutar($sqlDel, [$ahora, $hospedajeID, $empresaID]) === false) {
        throw new Exception("Error al anular el registro de hospedaje.");
    }

    // 4. Pasar habitación a estado LIMPIEZA
    $sqlHab = "UPDATE habitaciones SET estado = 'LIMPIEZA', _fec_modificacion = ? WHERE habitacionID = ?";
    if ($db->ejecutar($sqlHab, [$ahora, $habitacionID]) === false) {
        throw new Exception("Error al actualizar estado de la habitación.");
    }

    // Nota: El trigger 'tr_eliminar_movimientos_hospedaje' se encarga de los movimientos de caja.

    $db->commit();

    $_SESSION['mensaje'] = "Hospedaje eliminado y auditado. Habitación en LIMPIEZA.";
    $_SESSION['mensaje_tipo'] = "success";

    header("Location: hospedajes.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    die("Error crítico: " . $e->getMessage());
}
?>
