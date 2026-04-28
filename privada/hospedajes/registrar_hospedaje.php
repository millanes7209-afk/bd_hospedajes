<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE REGISTRO TRANSACCIONAL DE HOSPEDAJE
 * Auditoría estricta y pagos híbridos.
 */
// 1. RECOLECTAR DATOS DEL FORMULARIO (ACCESO DIRECTO - DEBE EXISTIR PARA PROSEGUIR)
$habitacionID = $_POST['habitacionID'];
$tipo_estadia = $_POST['tipo'];
$monto_total = $_POST['monto_total'];
$checkout = $_POST['checkout'];
$descripcion = $_POST['descripcion'];
$habitacion_numero = $_POST['habitacion_numero'];

// 2. DATOS DE SESIÓN Y AUDITORÍA
$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];
$cajaID = $_SESSION['caja_abierta_id'];
$ahora = date("Y-m-d H:i:s");

// Listas de Clientes y Pagos
$clientes = $_POST['clientesSeleccionados'] ?? [];
$pagos = $_POST['pagos'] ?? [];

// 3. VALIDACIONES BÁSICAS DE SEGURIDAD
if (!$habitacionID || empty($clientes) || ($monto_total > 0 && empty($pagos)) || !$empresaID || !$usuarioID) {
    $_SESSION['mensaje'] = "Error: Datos de registro incompletos o falta registrar forma de pago.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: ../habitacioness/habitaciones.php");
    exit();
}

if (!$cajaID) {
    $_SESSION['mensaje'] = "Error: Debe tener una caja abierta para registrar pagos.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: ../habitacioness/habitaciones.php");
    exit();
}

try {
    // INICIAMOS TRANSACCIÓN PARA GARANTIZAR ATOMICIDAD
    if (!$db->beginTransaction()) {
        throw new Exception("No se pudo iniciar la transacción en la base de datos.");
    }

    // 4. DETERMINAR LA CUENTA CONTABLE
    $codigo_cuenta = ($tipo_estadia == 'MOMENTANEO') ? '402' : '401';
    $cuenta = $db->obtenerFila("SELECT cuentaID FROM cuentas WHERE codigo = ? AND empresaID = ?", [$codigo_cuenta, $empresaID]);
    
    if (!$cuenta) {
        throw new Exception("Error Contable: No se encontró la cuenta [$codigo_cuenta] configurada para esta empresa.");
    }
    $cuentaID = $cuenta['cuentaID'];

    // 5. INSERTAR EN LA SUPER-TABLA INGRESOS (Cabecera única)
    $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $concepto_ingreso = "$tipo_estadia HAB. $habitacion_numero" . ($descripcion ? " - $descripcion" : "");
    $paramsI = [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_total, $concepto_ingreso, $ahora, $usuarioID];
    
    if ($db->ejecutar($sqlI, $paramsI) === false) {
        throw new Exception("Error BD: No se pudo registrar el ingreso maestro.");
    }
    $ingresoID = $db->lastInsertId();

    // 6. DETALLE DE PAGOS (ingreso_pagos)
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        if ($monto_pago > 0) {
            $sqlIP = "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)";
            if ($db->ejecutar($sqlIP, [$ingresoID, $pago['formaPagoID'], $monto_pago]) === false) {
                throw new Exception("Error BD: No se pudo registrar el desglose del pago.");
            }
        }
    }

    // 7. INSERTAR HOSPEDAJE (Vinculado al ingresoID)
    $sqlH = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, ingresoID, checkin, checkout, monto, estado, observaciones, 
                                   _fec_insercion, _fec_modificacion, _estado, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsH = [
        $empresaID, $habitacionID, $cajaID, $ingresoID, $ahora, $checkout, $monto_total, 
        'ACTIVO', $descripcion, $ahora, $ahora, 'A', $usuarioID
    ];
    
    if ($db->ejecutar($sqlH, $paramsH) === false) {
        throw new Exception("Error BD: No se pudo registrar el hospedaje vinculado.");
    }
    
    $hospedajeID = $db->lastInsertId();

    if (!$hospedajeID) {
        throw new Exception("No se pudo obtener el ID del hospedaje registrado.");
    }

    // 8. VINCULAR CLIENTES
    foreach ($clientes as $clienteID) {
        $sqlC = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, 
                                                _fec_insercion, _fec_modificacion, _estado, _usuario) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
                 
        if ($db->ejecutar($sqlC, [$empresaID, $hospedajeID, $clienteID, $ahora, $ahora, 'A', $usuarioID]) === false) {
            throw new Exception("Error al vincular el cliente ID: {$clienteID}");
        }
    }

    // 7. ACTUALIZAR ESTADO DE LA HABITACIÓN
    $sqlHab = "UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?";
    if ($db->ejecutar($sqlHab, [$habitacionID]) === false) {
        throw new Exception("Error BD: No se pudo actualizar el estado de la habitación.");
    }

    // TODO SALIÓ BIEN - CONFIRMAMOS CAMBIOS
    $db->commit();

    $_SESSION['mensaje'] = "Hospedaje registrado correctamente en Habitacion " . $habitacion_numero;
    $_SESSION['mensaje_tipo'] = "success";
    
    // Redirección exitosa usando explícitamente habitacioness
    header("Location: ../habitacioness/habitaciones.php");
    exit();

} catch (Exception $e) {
    // SI ALGO FALLA, DESHACEMOS TODO AUTOMÁTICAMENTE
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    // ==========================================
    // DEPURADOR EN PANTALLA (DETIENE LA REDIRECCIÓN)
    // ==========================================
    echo "<div style='background-color:#fee; padding:25px; border:3px solid #c00; font-family:sans-serif; margin:20px; border-radius:8px;'>";
    echo "<h2 style='color:#c00; margin-top:0;'>¡ERROR CRÍTICO DURANTE EL REGISTRO!</h2>";
    echo "<p><b>La transacción de base de datos ha sido cancelada (Rollback). Ningún dato se ha guardado para evitar inconsistencias.</b></p>";
    echo "<hr>";
    echo "<h4 style='color:#333;'>Información del Depurador:</h4>";
    echo "<ul style='color:#555;'>";
    echo "<li><b>Mensaje de Error:</b> " . $e->getMessage() . "</li>";
    echo "<li><b>Archivo:</b> " . basename($e->getFile()) . "</li>";
    echo "<li><b>Línea que falló:</b> " . $e->getLine() . "</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<p><small>Por favor verifica que la estructura de tus tablas (especialmente 'movimientos') coincida exactamente con los datos que se están enviando.</small></p>";
    echo "<a href='window.history.back()' onclick='window.history.back(); return false;' style='display:inline-block; margin-top:15px; padding:10px 20px; background:#0056b3; color:white; text-decoration:none; border-radius:5px;'>Volver al Formulario</a>";
    echo "</div>";
    exit();
}
?>