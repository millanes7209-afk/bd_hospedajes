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

    // 2. SNAPSHOT INICIAL (La "foto" metabórica de los datos antes del cambio)
    $sqlOrg = "SELECT monto FROM hospedajes WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    $hosp_org = $db->obtenerFila($sqlOrg, [$hospedajeID, $empresaID]);
    if (!$hosp_org) {
        throw new Exception("Hospedaje no encontrado.");
    }
    $monto_anterior = $hosp_org['monto'];

    // Obtener desglose de pagos ORIGINAL
    $sqlPagOrig = "SELECT fp.tipo, m.monto 
                   FROM movimientos m 
                   INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID 
                   WHERE m.referenciaID = ? AND m.categoria IN ('HOSPEDAJE', 'MOMENTANEO') AND m._estado = 'A'";
    $pagos_originales = $db->obtenerTodo($sqlPagOrig, [$hospedajeID]);
    
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

    // 3. CONSTRUIR SNAPSHOT NUEVO (Percepción del sistema)
    // Mapeamos los nombres de las formas de pago para el JSON de auditoría
    $sqlFP = "SELECT formaPagoID, tipo FROM formas_pago WHERE _estado <> 'X'";
    $rsFP = $db->obtenerTodo($sqlFP);
    $nombresFP = [];
    foreach($rsFP as $f) $nombresFP[$f['formaPagoID']] = $f['tipo'];

    // Normalizar montos nuevos
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

    // 4. DETECTAR SI HUBO CAMBIOS QUE REQUIERAN AUDITORÍA (Monto o Distribución)
    $cambioFinanciero = ($detalle_original !== $detalle_nuevo || abs($monto_nuevo - $monto_anterior) > 0.01);

    if ($cambioFinanciero) {
        // Si el sistema detecta cambio pero el usuario no justificó (saltándose el modal)
        if (!$motivo_auditoria) {
             throw new Exception("Error de Seguridad: Se detectó un cambio financiero sin justificación.");
        }

        $sqlLog = "INSERT INTO hospedajes_auditoria_montos 
                   (hospedajeID, tipo_auditoria, monto_anterior, monto_nuevo, detalle_original, detalle_nuevo, motivo, usuarioID, fecha, empresaID) 
                   VALUES (?, 'MODIFICACION', ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($db->ejecutar($sqlLog, [$hospedajeID, $monto_anterior, $monto_nuevo, $detalle_original, $detalle_nuevo, $motivo_auditoria, $usuarioID, $ahora, $empresaID]) === false) {
            throw new Exception("No se pudo registrar la auditoría de modificación.");
        }
    }

    // 5. ACTUALIZAR HOSPEDAJE (Multi-empresa)
    $sqlH = "UPDATE hospedajes 
             SET estado = ?, checkout = ?, monto = ?, observaciones = ?, _fec_modificacion = ?
             WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    if ($db->ejecutar($sqlH, [$estado, $checkout, $monto_nuevo, $descripcion, $ahora, $hospedajeID, $empresaID]) === false) {
        throw new Exception("Error al actualizar datos del hospedaje.");
    }

    // 6. ACTUALIZAR PAGOS
    foreach ($pagos_form as $pago) {
        $movID = $pago['movimientoID'] ?? null;
        if ($movID) {
            $sqlM = "UPDATE movimientos SET monto = ?, formapagoID = ?, _fec_modificacion = ? WHERE movimientoID = ? AND _estado <> 'X'";
            if ($db->ejecutar($sqlM, [$pago['monto'], $pago['formaPagoID'], $ahora, $movID]) === false) {
                throw new Exception("Error al actualizar pago ID: {$movID}.");
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
