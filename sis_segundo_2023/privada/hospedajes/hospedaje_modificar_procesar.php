<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE PROCESAMIENTO DE MODIFICACIÓN DE HOSPEDAJE
 * Incluye Auditoría Forense de Montos y Distribución de Pagos (Barajado).
 */

// 1. RECOLECTAR DATOS
$hospedajeID = $_POST['hospedajeID'] ?? null;
$estado = $_POST['estado'] ?? null;
$checkout = $_POST['checkout'] ?? null;
$monto_nuevo = $_POST['monto_total'] ?? 0;
$descripcion = $_POST['descripcion'] ?? '';
$pagos_form = $_POST['pagos'] ?? [];
$motivo_auditoria = $_POST['motivo_auditoria'] ?? null;

$usuarioID = $_SESSION["sesion_id_usuario"] ?? null;
$empresaID = $_SESSION["empresaID"] ?? null;
$ahora = date("Y-m-d H:i:s");

if (!$hospedajeID || !$usuarioID || !$empresaID) {
    die("Error: Sesión expirada o datos incompletos.");
}

try {
    if (!$db->beginTransaction()) {
        throw new Exception("No se pudo iniciar la transacción.");
    }

    // 2. SNAPSHOT INICIAL
    $sqlOrg = "SELECT monto, ingresoID FROM hospedajes WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    $hosp_org = $db->obtenerFila($sqlOrg, [$hospedajeID, $empresaID]);
    if (!$hosp_org) {
        throw new Exception("Hospedaje no encontrado.");
    }
    $monto_anterior = $hosp_org['monto'];
    $ingresoID = $hosp_org['ingresoID'];

    // Obtener desglose de pagos ORIGINAL (desde ingreso_pagos)
    $sqlPagOrig = "SELECT fp.tipo, ip.monto 
                   FROM ingreso_pagos ip 
                   INNER JOIN formas_pago fp ON ip.formapagoID = fp.formapagoID 
                   WHERE ip.ingresoID = ?";
    $pagos_originales = $db->obtenerTodo($sqlPagOrig, [$ingresoID]);
    
    // Normalizar montos originales
    $pagos_audit_orig = [];
    foreach($pagos_originales as $po) {
        $pagos_audit_orig[] = [
            "tipo" => $po['tipo'],
            "monto" => number_format((float)$po['monto'], 2, '.', '')
        ];
    }
    usort($pagos_audit_orig, function($a, $b) { return strcmp($a['tipo'], $b['tipo']); });
    $detalle_original = json_encode($pagos_audit_orig);

    // 3. CONSTRUIR SNAPSHOT NUEVO
    $sqlFP = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado <> 'X' AND empresaID = ?";
    $rsFP = $db->obtenerTodo($sqlFP, [$empresaID]);
    $nombresFP = [];
    foreach($rsFP as $f) $nombresFP[$f['formaPagoID']] = $f['tipo'];

    $pagos_audit_nuevo = [];
    foreach($pagos_form as $p) {
        if (!isset($p['monto'])) continue;
        $pagos_audit_nuevo[] = [
            "tipo" => $nombresFP[$p['formaPagoID']] ?? "Desconocido",
            "monto" => number_format((float)$p['monto'], 2, '.', '')
        ];
    }
    usort($pagos_audit_nuevo, function($a, $b) { return strcmp($a['tipo'], $b['tipo']); });
    $detalle_nuevo = json_encode($pagos_audit_nuevo);

    // 4. AUDITORÍA SI HAY CAMBIOS
    $cambioFinanciero = ($detalle_original !== $detalle_nuevo || abs($monto_nuevo - $monto_anterior) > 0.01);

    if ($cambioFinanciero) {
        if (!$motivo_auditoria) {
             throw new Exception("Error de Seguridad: Se detectó un cambio financiero sin justificación.");
        }

        $sqlLog = "INSERT INTO auditorias 
                   (hospedajeID, tipo_auditoria, monto_anterior, monto_nuevo, detalle_original, detalle_nuevo, motivo, usuarioID, fecha, empresaID) 
                   VALUES (?, 'MODIFICACION', ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($db->ejecutar($sqlLog, [$hospedajeID, $monto_anterior, $monto_nuevo, $detalle_original, $detalle_nuevo, $motivo_auditoria, $usuarioID, $ahora, $empresaID]) === false) {
            throw new Exception("No se pudo registrar la auditoría de modificación.");
        }
    }

    // 5. ACTUALIZAR HOSPEDAJE
    $sqlH = "UPDATE hospedajes 
             SET estado = ?, checkout = ?, monto = ?, observaciones = ?, _fec_modificacion = ?
             WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    if ($db->ejecutar($sqlH, [$estado, $checkout, $monto_nuevo, $descripcion, $ahora, $hospedajeID, $empresaID]) === false) {
        throw new Exception("Error al actualizar datos del hospedaje.");
    }

    // 6. ACTUALIZAR CONTABILIDAD (ingresos e ingreso_pagos)
    // Sincronizar monto total del ingreso
    $db->ejecutar("UPDATE ingresos SET monto_total = ?, _fec_modificacion = ? WHERE ingresoID = ? AND empresaID = ?", [$monto_nuevo, $ahora, $ingresoID, $empresaID]);

    foreach ($pagos_form as $pago) {
        $ipID = $pago['movimientoID'] ?? null; // Reutilizamos el nombre del campo del POST (que ahora contendrá el ingresopagoID)
        if ($ipID) {
            $sqlIP = "UPDATE ingreso_pagos SET monto = ?, formapagoID = ? WHERE ingresopagoID = ?";
            if ($db->ejecutar($sqlIP, [$pago['monto'], $pago['formaPagoID'], $ipID]) === false) {
                throw new Exception("Error al actualizar detalle de pago.");
            }
        }
    }

    $db->commit();
    $_SESSION['mensaje'] = "Cambios guardados y fiscalizados correctamente.";
    header("Location: hospedajes.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Error crítico: " . $e->getMessage());
}
?>
