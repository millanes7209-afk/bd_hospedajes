<?php
session_start();
require_once("../../conexion.php");

// Validar que exista una caja abierta
if (!isset($_SESSION['caja_abierta_id']) || empty($_SESSION['caja_abierta_id'])) {
    $_SESSION['mensaje'] = "Error: No hay una caja abierta para este usuario.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresaID = $_SESSION['empresaID'];
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $cajaID = $_SESSION['caja_abierta_id'];
    
    $monto = (float)$_POST['monto'];
    $tipo_mov = $_POST['tipo_movimiento']; // INGRESO o EGRESO
    $cuentaID = (int)$_POST['cuentaID'];
    $descripcion = strtoupper(trim($_POST['descripcion']));
    $formaPagoID = (int)$_POST['formaPagoID'];
    
    // Iniciar Transacción
    $db->beginTransaction();

    try {
        if ($tipo_mov === 'INGRESO') {
            // 1. INSERTAR INGRESO MAESTRO
            $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $db->ejecutar($sqlI, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto, $descripcion, $usuarioID]);
            $ingresoID = $db->lastInsertId();

            // 2. DETALLE DE PAGO
            $db->ejecutar("INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)", [$ingresoID, $formaPagoID, $monto]);

            // 3. VINCULAR A SERVICIOS EXTRA (Opcional, manteniendo compatibilidad con histórico)
            $sql_se = "INSERT INTO servicios_extra (empresaID, ingresoID, tipo, descripcion, monto, fecha, _fec_insercion, _usuario, _estado) 
                       VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?, 'A')";
            $db->ejecutar($sql_se, [$empresaID, $ingresoID, 'OTROS', $descripcion, $monto, $usuarioID]);

        } else {
            // 1. INSERTAR EGRESO MAESTRO
            $sqlE = "INSERT INTO egresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
                     VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)";
            $db->ejecutar($sqlE, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto, $descripcion, $usuarioID]);
            $egresoID = $db->lastInsertId();

            // 2. DETALLE DE PAGO
            $db->ejecutar("INSERT INTO egreso_pagos (egresoID, formapagoID, monto) VALUES (?, ?, ?)", [$egresoID, $formaPagoID, $monto]);
        }

        $db->commit();
        $_SESSION['mensaje'] = "Movimiento de $tipo_mov registrado correctamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['mensaje'] = "Error al procesar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: habitaciones.php");
    exit();
}
?>
