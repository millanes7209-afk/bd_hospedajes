<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE PROCESAMIENTO DE PERMANENCIA
 * Finaliza estancia previa y genera una nueva con continuidad de huéspedes.
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
    echo "Error: Debe registrar al menos una forma de pago para el monto ingresado.";
    exit;
}

if (!$hospedajeID_anterior || !$habitacionID || empty($clientes) || !$cajaID) {
    echo "Error: Datos incompletos para procesar la permanencia.";
    exit;
}

try {
    // REGLA DE ORO: Validar auditoría antes de cualquier cambio financiero
    $sqlAudit = "SELECT hospedajeID FROM hospedajes 
                 WHERE hospedajeID = ? AND empresaID = ? AND estado = 'ACTIVO' AND _estado <> 'X'";
    $audit = $db->obtenerFila($sqlAudit, [$hospedajeID_anterior, $empresaID]);

    if (!$audit) {
        throw new Exception("Error de Seguridad: No tiene permisos o el hospedaje no está activo.");
    }

    $db->beginTransaction();

    // 1. FINALIZAR HOSPEDAJE ANTERIOR
    $sqlOld = "UPDATE hospedajes SET estado = 'INACTIVO', observaciones = CONCAT(observaciones, ' | EXTENDIDO POR PERMANENCIA') 
               WHERE hospedajeID = ?";
    $db->ejecutar($sqlOld, [$hospedajeID_anterior]);

    // 2. REGISTRO CONTABLE INTELIGENTE (Hereda el tipo del hospedaje anterior)
    // Buscamos el código de cuenta del ingreso anterior (401 o 402)
    $sqlTipoAnterior = "SELECT c.codigo, c.cuentaID 
                        FROM hospedajes h
                        JOIN ingresos i ON h.ingresoID = i.ingresoID
                        JOIN cuentas c ON i.cuentaID = c.cuentaID
                        WHERE h.hospedajeID = ?";
    $tipoAnterior = $db->obtenerFila($sqlTipoAnterior, [$hospedajeID_anterior]);
    
    if (!$tipoAnterior) {
        // Fallback si no se encuentra (por registros antiguos): usar Hospedaje (401)
        $codigo_cuenta = '401';
        $cuenta = $db->obtenerFila("SELECT cuentaID FROM cuentas WHERE codigo = '401' AND empresaID = ?", [$empresaID]);
        $cuentaID = $cuenta['cuentaID'];
    } else {
        $codigo_cuenta = $tipoAnterior['codigo'];
        $cuentaID = $tipoAnterior['cuentaID'];
    }

    $tipo_label = ($codigo_cuenta == '402') ? 'MOMENTANEO' : 'HOSPEDAJE';

    // Cabecera de Ingreso
    $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $concepto_i = "EXTENSIÓN $tipo_label HAB. $habitacion_numero" . ($descripcion ? " - $descripcion" : "");
    $paramsI = [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_total, $concepto_i, $ahora, $usuarioID];
    
    if ($db->ejecutar($sqlI, $paramsI) === false) throw new Exception("Error al registrar el ingreso maestro.");
    $ingresoID = $db->lastInsertId();

    // Detalle de Pagos
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        if ($monto_pago > 0) {
            $sqlIP = "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)";
            if ($db->ejecutar($sqlIP, [$ingresoID, $pago['formaPagoID'], $monto_pago]) === false) {
                throw new Exception("Error al registrar el desglose del pago.");
            }
        }
    }

    // 3. CREAR NUEVO HOSPEDAJE (LA PERMANENCIA) vinculado al ingresoID
    $sqlNew = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, ingresoID, checkin, checkout, monto, estado, observaciones, 
                                     _fec_insercion, _fec_modificacion, _estado, _usuario) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsNew = [
        $empresaID, $habitacionID, $cajaID, $ingresoID, $ahora, $checkout, $monto_total, 
        'ACTIVO', $descripcion, $ahora, $ahora, 'A', $usuarioID
    ];
    $db->ejecutar($sqlNew, $paramsNew);
    $nuevoHospedajeID = $db->lastInsertId();

    // 4. VINCULAR TODOS LOS CLIENTES AL NUEVO REGISTRO
    foreach ($clientes as $clienteID) {
        $sqlC = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, 
                                                _fec_insercion, _fec_modificacion, _estado, _usuario) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->ejecutar($sqlC, [$empresaID, $nuevoHospedajeID, $clienteID, $ahora, $ahora, 'A', $usuarioID]);
    }

    // 5. ASEGURAR QUE LA HABITACIÓN VUELVA A ESTADO OCUPADA (Limpiar DEUDA)
    $sqlHab = "UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?";
    $db->ejecutar($sqlHab, [$habitacionID]);

    $db->commit();
    $_SESSION['mensaje'] = "Permanencia ($tipo_label) registrada correctamente en Habitación " . $habitacion_numero;
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: ../habitacioness/habitaciones.php");

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error crítico: " . $e->getMessage();
    exit;
}
?>
