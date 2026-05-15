<?php
session_start();
require_once("../../conexion.php");

$descripcion  = strtoupper($_POST['descripcion'] ?? '');   // número de habitación (readonly)
$habitacionID = (int) $_POST["habitacionID"];
$monto_total  = floatval($_POST['monto_total']);
$pagos        = $_POST['pagos'] ?? [];
$cajaID       = $_SESSION['caja_abierta_id'];
$empresaID    = $_SESSION['empresaID'];
$usuarioID    = $_SESSION["sesion_id_usuario"];
$fecha_ahora  = date('Y-m-d H:i:s');

try {
    // --- Validaciones previas ---
    if (!$habitacionID || $monto_total <= 0 || empty($pagos) || !$cajaID) {
        throw new Exception("Datos incompletos. Verifica el monto, la forma de pago y que haya una caja abierta.");
    }

    $db->beginTransaction();

    // 1. Buscar cuenta contable para MOMENTÁNEO (código 402)
    $cuenta = $db->obtenerFila(
        "SELECT cuentaID FROM cuentas WHERE empresaID = ? AND codigo = '402' AND _estado <> 'X' LIMIT 1",
        [$empresaID]
    );
    if (!$cuenta) {
        // Fallback: primera cuenta INGRESO disponible
        $cuenta = $db->obtenerFila(
            "SELECT cuentaID FROM cuentas WHERE empresaID = ? AND tipo = 'INGRESO' AND _estado <> 'X' ORDER BY codigo ASC LIMIT 1",
            [$empresaID]
        );
    }
    if (!$cuenta) {
        throw new Exception("No se encontró cuenta de ingreso para esta empresa. Verifique el catálogo de cuentas.");
    }
    $cuentaID = $cuenta['cuentaID'];

    // 2. Insertar cabecera de ingreso
    $concepto = "MOMENTANEO HAB-$descripcion";
    $db->ejecutar(
        "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _fec_insercion, _usuario, _estado)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')",
        [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_total, $concepto, $fecha_ahora, $fecha_ahora, $usuarioID]
    );
    $ingresoID = $db->lastInsertId();

    // 3. Insertar detalle de pagos (ingreso_pagos) — permite QR + Efectivo
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto'] ?? 0));
        if ($monto_pago > 0) {
            $db->ejecutar(
                "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)",
                [$ingresoID, $pago['formaPagoID'], $monto_pago]
            );
        }
    }

    // 4. Cambiar estado habitación a MOMENTANEO
    $db->ejecutar(
        "UPDATE habitaciones SET estado = 'MOMENTANEO' WHERE habitacionID = ?",
        [$habitacionID]
    );

    $db->commit();

    $_SESSION['mensaje']      = "Momentáneo registrado. Ingreso contabilizado en caja.";
    $_SESSION['mensaje_tipo'] = "success";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $_SESSION['mensaje']      = "Error al registrar: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "danger";
}

header("Location: habitaciones.php");
exit();
?>
