<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE PROCESAMIENTO DE PERMANENCIA (EXTENSIÓN DE ESTADÍA)
 * Finaliza estancia previa (ACTIVO o DEUDA) y genera una nueva.
 */

$hospedajeID_anterior = $_POST['hospedajeID_anterior'];
$habitacionID = $_POST['habitacionID'];
$checkout = $_POST['checkout'];
$monto_total = $_POST['monto_total'];
$descripcion = $_POST['descripcion'];
$habitacion_numero = $_POST['habitacion_numero'];

$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];
$cajaID = $_SESSION['caja_abierta_id'];
$ahora = date("Y-m-d H:i:s");

// Listas de Clientes y Pagos
$clientes = $_POST['clientesSeleccionados'] ?? [];
$pagos = $_POST['pagos'] ?? [];

if ($monto_total > 0 && empty($pagos)) {
    die("Error: Debe registrar al menos una forma de pago para el monto ingresado.");
}

if (!$hospedajeID_anterior || !$habitacionID || empty($clientes) || !$cajaID) {
    die("Error: Datos incompletos para procesar la permanencia o caja cerrada.");
}

try {
    // 1. VALIDACIÓN DE PERMISOS Y ESTADO (Permitir ACTIVO y DEUDA)
    $sqlAudit = "SELECT hospedajeID FROM hospedajes 
                 WHERE hospedajeID = ? AND empresaID = ? AND estado IN ('ACTIVO', 'DEUDA') AND _estado <> 'X'";
    $audit = $db->obtenerFila($sqlAudit, [$hospedajeID_anterior, $empresaID]);

    if (!$audit) {
        throw new Exception("Error de Seguridad: Hospedaje no encontrado o ya fue finalizado.");
    }

    $db->beginTransaction();

    // 2. FINALIZAR HOSPEDAJE ANTERIOR
    $sqlOld = "UPDATE hospedajes SET estado = 'FINALIZADO', observaciones = CONCAT(observaciones, ' | EXTENDIDO POR PERMANENCIA') 
               WHERE hospedajeID = ? AND empresaID = ?";
    $db->ejecutar($sqlOld, [$hospedajeID_anterior, $empresaID]);

    // 3. REGISTRO CONTABLE (Heredar cuenta contable del anterior)
    $sqlTipoAnterior = "SELECT c.codigo, c.cuentaID 
                        FROM hospedajes h
                        JOIN ingresos i ON h.ingresoID = i.ingresoID
                        JOIN cuentas c ON i.cuentaID = c.cuentaID
                        WHERE h.hospedajeID = ? AND h.empresaID = ?";
    $tipoAnterior = $db->obtenerFila($sqlTipoAnterior, [$hospedajeID_anterior, $empresaID]);
    
    if (!$tipoAnterior) {
        $cuenta = $db->obtenerFila("SELECT cuentaID FROM cuentas WHERE codigo = '401' AND empresaID = ?", [$empresaID]);
        $cuentaID = $cuenta['cuentaID'];
        $tipo_label = 'HOSPEDAJE';
    } else {
        $cuentaID = $tipoAnterior['cuentaID'];
        $tipo_label = ($tipoAnterior['codigo'] == '402') ? 'MOMENTANEO' : 'HOSPEDAJE';
    }

    // Insertar Ingreso Maestro
    $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $concepto_i = "EXTENSIÓN $tipo_label HAB. $habitacion_numero" . ($descripcion ? " - $descripcion" : "");
    $db->ejecutar($sqlI, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_total, $concepto_i, $ahora, $usuarioID]);
    $ingresoID = $db->ultimoInsertId();

    // Detalle de Pagos
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        if ($monto_pago > 0) {
            $sqlIP = "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)";
            $db->ejecutar($sqlIP, [$ingresoID, $pago['formaPagoID'], $monto_pago]);
        }
    }

    // 4. CREAR NUEVO HOSPEDAJE (CONTINUIDAD)
    $sqlNew = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, ingresoID, checkin, checkout, monto, estado, observaciones, 
                                     _fec_insercion, _fec_modificacion, _estado, _usuario) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsNew = [
        $empresaID, $habitacionID, $cajaID, $ingresoID, $ahora, $checkout, $monto_total, 
        'ACTIVO', $descripcion, $ahora, $ahora, 'A', $usuarioID
    ];
    $db->ejecutar($sqlNew, $paramsNew);
    $nuevoHospedajeID = $db->ultimoInsertId();

    // Vincular Clientes
    foreach ($clientes as $clienteID) {
        $sqlC = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, 
                                                 _fec_insercion, _fec_modificacion, _estado, _usuario) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->ejecutar($sqlC, [$empresaID, $nuevoHospedajeID, $clienteID, $ahora, $ahora, 'A', $usuarioID]);
    }

    // 5. ACTUALIZAR ESTADO DE HABITACIÓN (Cambio de "Esencia")
    // Forzamos a OCUPADA (eliminando el estado DEUDA si existía)
    $db->ejecutar("UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?", [$habitacionID]);

    $db->commit();
    $_SESSION['mensaje'] = "Permanencia registrada. Habitación $habitacion_numero ahora está OCUPADA.";
    $_SESSION['mensaje_tipo'] = "success";

    $_SESSION['debug_last_op'] = [
        'accion' => 'PERMANENCIA_EXTENSIÓN',
        'habitacionID' => $habitacionID,
        'numero' => $habitacion_numero,
        'nuevo_estado_db' => 'OCUPADA',
        'checkout_nuevo' => $checkout,
        'monto' => $monto_total
    ];

    header("Location: ../habitacioness/habitaciones.php");

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Error crítico: " . $e->getMessage());
}
?>
