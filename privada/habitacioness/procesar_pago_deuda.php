<?php
session_start();
require_once("../../conexion.php");

$hospedajeID  = $_POST["hospedajeID"]  ?? null;
$habitacionID = $_POST["habitacionID"] ?? null;
$monto_deuda  = floatval($_POST["monto_total"] ?? 0);
$formaPagoID  = $_POST["formaPagoID"]  ?? null;

$usuarioID  = $_SESSION["sesion_id_usuario"] ?? null;
$empresaID  = $_SESSION["empresaID"]         ?? null;
$ahora      = date("Y-m-d H:i:s");

if (!$hospedajeID || !$habitacionID || !$formaPagoID) {
    $_SESSION['mensaje']      = "Error: Datos incompletos. Debe seleccionar una forma de pago.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}

// Validar que exista una caja abierta
$caja = $db->obtenerFila(
    "SELECT cajaID FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ? AND empresaID = ? AND _estado <> 'X'",
    [$usuarioID, $empresaID]
);

if (!$caja) {
    $_SESSION['mensaje']      = "Error: Debe tener una caja abierta para registrar pagos.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}
$cajaID = $caja['cajaID'];

try {
    if (!$db->beginTransaction()) throw new Exception("No se pudo iniciar la transacción.");

    // 1. Marcar el hospedaje original como INACTIVO y actualizar checkout
    $sql_hospedaje = "UPDATE hospedajes 
                      SET estado = 'INACTIVO',
                          checkout = ?,
                          _fec_modificacion = ?
                      WHERE hospedajeID = ?";
    if ($db->ejecutar($sql_hospedaje, [$ahora, $ahora, $hospedajeID]) === false) {
        throw new Exception("Error al cerrar el hospedaje original.");
    }

    // 2. Procesar lo financiero (solo si hay monto > 0)
    if ($monto_deuda > 0) {
        // HERENCIA INTELIGENTE: Buscamos el tipo del hospedaje que estamos cerrando
        $sqlTipoOriginal = "SELECT c.codigo, c.cuentaID 
                            FROM hospedajes h
                            JOIN ingresos i ON h.ingresoID = i.ingresoID
                            JOIN cuentas c ON i.cuentaID = c.cuentaID
                            WHERE h.hospedajeID = ?";
        $tipoOriginal = $db->obtenerFila($sqlTipoOriginal, [$hospedajeID]);

        if (!$tipoOriginal) {
            // Fallback: Hospedaje (401)
            $codigo_cuenta = '401';
            $cuenta = $db->obtenerFila("SELECT cuentaID FROM cuentas WHERE codigo = '401' AND empresaID = ?", [$empresaID]);
            $cuentaID = $cuenta['cuentaID'];
        } else {
            $codigo_cuenta = $tipoOriginal['codigo'];
            $cuentaID = $tipoOriginal['cuentaID'];
        }

        $tipo_label = ($codigo_cuenta == '402') ? 'MOMENTANEO' : 'HOSPEDAJE';

        // A. Cabecera de Ingreso
        $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $concepto = "PAGO DEUDA $tipo_label SALIDA HAB. " . $_POST['habitacion_numero'];
        if ($db->ejecutar($sqlI, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_deuda, $concepto, $ahora, $usuarioID]) === false) {
            throw new Exception("Error al registrar el ingreso maestro.");
        }
        $ingresoID = $db->lastInsertId();

        // B. Detalle de Pago
        $sqlIP = "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)";
        if ($db->ejecutar($sqlIP, [$ingresoID, $formaPagoID, $monto_deuda]) === false) {
            throw new Exception("Error al registrar el desglose del pago.");
        }

        // C. Nuevo Registro de Hospedaje Técnico (Nace FINALIZADO/INACTIVO)
        // Esto evita pagos huérfanos y mantiene la trazabilidad en la tabla hospedajes
        $checkout_tecnico = date("Y-m-d H:i:s", strtotime($ahora . " +1 hour"));
        $sqlNewH = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, ingresoID, checkin, checkout, monto, estado, observaciones, 
                                          _fec_insercion, _fec_modificacion, _estado, _usuario) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $paramsNewH = [
            $empresaID, $habitacionID, $cajaID, $ingresoID, $ahora, $checkout_tecnico, $monto_deuda, 
            'FINALIZADO', 'REGISTRO POR PAGO DE DEUDA AL SALIR', $ahora, $ahora, 'A', $usuarioID
        ];
        if ($db->ejecutar($sqlNewH, $paramsNewH) === false) {
            throw new Exception("Error al vincular el pago con un nuevo registro de hospedaje.");
        }
    }

    // 3. Pasar la habitación a estado LIMPIEZA
    $sql_limpieza = "UPDATE habitaciones SET estado = 'LIMPIEZA', _fec_modificacion = ? WHERE habitacionID = ?";
    if ($db->ejecutar($sql_limpieza, [$ahora, $habitacionID]) === false) {
        throw new Exception("Error al liberar la habitación.");
    }

    $db->commit();

    $_SESSION['mensaje']      = "Pago registrado. Habitación pasada a LIMPIEZA.";
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: habitaciones.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("<div style='color:red; font-weight:bold; padding:20px;'>Error crítico: " . $e->getMessage() . "</div>");
}
?>

