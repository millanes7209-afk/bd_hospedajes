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

    $monto = (float) $_POST['monto'];
    $tipo_mov = $_POST['tipo_movimiento']; // INGRESO o EGRESO
    $cuentaID = (int) $_POST['cuentaID'];
    $descripcion = strtoupper(trim($_POST['descripcion']));
    $formaPagoID = (int) $_POST['formaPagoID'];

    // Iniciar Transacción
    $db->beginTransaction();

    try {
        $fecha_actual = date('Y-m-d H:i:s');

        if ($tipo_mov === 'INGRESO') {
            // 1. INSERTAR INGRESO MAESTRO
            $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario, _fec_insercion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->ejecutar($sqlI, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto, $descripcion, $fecha_actual, $usuarioID, $fecha_actual]);
            $ingresoID = $db->lastInsertId();

            // 2. DETALLE DE PAGO
            $db->ejecutar("INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto, _fec_insercion) VALUES (?, ?, ?, ?)", [$ingresoID, $formaPagoID, $monto, $fecha_actual]);

            // 3. Registro completado

        } else {
            // 1. INSERTAR EGRESO MAESTRO
            $sqlE = "INSERT INTO egresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario, _fec_insercion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->ejecutar($sqlE, [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto, $descripcion, $fecha_actual, $usuarioID, $fecha_actual]);
            $egresoID = $db->lastInsertId();

            // 2. DETALLE DE PAGO
            $db->ejecutar("INSERT INTO egreso_pagos (egresoID, formapagoID, monto, _fec_insercion) VALUES (?, ?, ?, ?)", [$egresoID, $formaPagoID, $monto, $fecha_actual]);
        }

        $db->commit();
        $_SESSION['mensaje'] = "Movimiento de $tipo_mov registrado correctamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if ($db->inTransaction())
            $db->rollBack();
        $_SESSION['mensaje'] = "Error al procesar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: habitaciones.php");
    exit();
}
?>