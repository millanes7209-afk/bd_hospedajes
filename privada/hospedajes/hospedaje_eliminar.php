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
$caja_abierta_id = $_SESSION["caja_abierta_id"] ?? null;
$ahora = date("Y-m-d H:i:s");

if (!$hospedajeID || !$usuarioID || !$empresaID || !$caja_abierta_id) {
    die("Error: No tiene una caja abierta o su sesión ha expirado.");
}

try {
    if (!$db->beginTransaction()) {
        throw new Exception("No se pudo iniciar la transacción.");
    }

    // 1. Obtener datos previos para la auditoría y validación de seguridad
    $sqlHosp = "SELECT habitacionID, monto, ingresoID, cajaID FROM hospedajes WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    $hospedaje = $db->obtenerFila($sqlHosp, [$hospedajeID, $empresaID]);

    if (!$hospedaje) {
        throw new Exception("Hospedaje no encontrado o ya eliminado.");
    }

    // BLOQUEO DE SEGURIDAD: Solo se puede eliminar si pertenece a la caja abierta actual
    if ($hospedaje['cajaID'] != $caja_abierta_id) {
        throw new Exception("ACCESO DENEGADO: No puede eliminar un registro que pertenece a otro turno o caja. Por favor, contacte con el administrador.");
    }

    $habitacionID = $hospedaje['habitacionID'];
    $montoAnterior = $hospedaje['monto'];
    $ingresoID = $hospedaje['ingresoID'];

    // Obtener desglose de pagos desde ingreso_pagos para auditoría
    $sqlPagos = "SELECT fp.tipo, ip.monto 
                 FROM ingreso_pagos ip 
                 INNER JOIN formas_pago fp ON ip.formapagoID = fp.formapagoID 
                 WHERE ip.ingresoID = ?";
    $pagosOriginales = $db->obtenerTodo($sqlPagos, [$ingresoID]);
    $detalleOriginal = json_encode($pagosOriginales);

    // 2. Registrar en la Tabla de Auditoría (Antes de borrar)
    $sqlAudit = "INSERT INTO auditorias 
                 (hospedajeID, tipo_auditoria, monto_anterior, monto_nuevo, detalle_original, detalle_nuevo, motivo, usuarioID, fecha, empresaID) 
                 VALUES (?, 'ELIMINACION', ?, 0, ?, 'CANCELADO', ?, ?, ?, ?)";
    
    if ($db->ejecutar($sqlAudit, [$hospedajeID, $montoAnterior, $detalleOriginal, $motivo, $usuarioID, $ahora, $empresaID]) === false) {
        throw new Exception("No se pudo registrar la auditoría de eliminación.");
    }

    // 3. Anular contablemente el INGRESO maestro
    $sqlAnularI = "UPDATE ingresos SET _estado = 'X', _fec_modificacion = ? WHERE ingresoID = ?";
    if ($db->ejecutar($sqlAnularI, [$ahora, $ingresoID]) === false) {
        throw new Exception("Error al anular el ingreso financiero relacionado.");
    }

    // 4. Marcar Hospedaje como eliminado (_estado = 'X')
    $sqlDel = "UPDATE hospedajes SET _estado = 'X', _fec_modificacion = ? WHERE hospedajeID = ? AND empresaID = ?";
    if ($db->ejecutar($sqlDel, [$ahora, $hospedajeID, $empresaID]) === false) {
        throw new Exception("Error al anular el registro de hospedaje.");
    }

    // 5. Pasar habitación a estado LIMPIEZA
    $sqlHab = "UPDATE habitaciones SET estado = 'LIMPIEZA', _fec_modificacion = ? WHERE habitacionID = ?";
    if ($db->ejecutar($sqlHab, [$ahora, $habitacionID]) === false) {
        throw new Exception("Error al actualizar estado de la habitación.");
    }

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
