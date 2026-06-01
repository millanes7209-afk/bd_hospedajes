<?php
session_start();
require_once("../../conexion.php");

/**
 * PROCESADOR EXCLUSIVO PARA REGISTRO DE MOMENTÁNEOS (SIN CARNET)
 */
$habitacionID = $_POST['habitacionID'];
$monto_total = $_POST['monto_total'];
$checkout = $_POST['checkout'];
$descripcion = $_POST['descripcion'] ?? 'REGISTRO MOMENTÁNEO';
$habitacion_numero = $_POST['habitacion_numero'];

$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];
$cajaID = $_SESSION['caja_abierta_id'];
$ahora = date("Y-m-d H:i:s");
$pagos = $_POST['pagos'] ?? [];

// Validaciones
if (!$habitacionID || empty($pagos) || !$cajaID) {
    $_SESSION['mensaje'] = "Error: Datos incompletos o caja cerrada.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: ../habitacioness/habitaciones.php");
    exit();
}

try {
    $db->beginTransaction();

    // VERIFICACIÓN ANTI-DUPLICADO: Rechazar si ya hay un hospedaje ACTIVO para esta habitación
    $hospedajeExistente = $db->obtenerFila(
        "SELECT hospedajeID FROM hospedajes 
         WHERE habitacionID = ? AND empresaID = ? AND estado = 'ACTIVO' AND _estado <> 'X'",
        [$habitacionID, $empresaID]
    );
    if ($hospedajeExistente) {
        throw new Exception("Esta habitación ya tiene un hospedaje activo (ID: {$hospedajeExistente['hospedajeID']}). No se puede registrar un duplicado.");
    }

    // 1. Insertar Hospedaje (Estado MOMENTANEO)
    $sqlH = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, checkin, checkout, monto, estado, observaciones, 
                                   _fec_insercion, _fec_modificacion, _estado, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsH = [
        $empresaID, $habitacionID, $cajaID, $ahora, $checkout, $monto_total, 
        'ACTIVO', $descripcion, $ahora, $ahora, 'A', $usuarioID
    ];
    $db->ejecutar($sqlH, $paramsH);
    $hospedajeID = $db->lastInsertId();

    // 2. Registrar Pagos (Categoría MOMENTANEO)
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        if ($monto_pago > 0) {
            $sqlM = "INSERT INTO movimientos (cajaID, empresaID, formapagoID, usuarioID, referenciaID, 
                                            tipo, categoria, monto, concepto, detalle, 
                                            _fec_insercion, _fec_modificacion, _estado, _usuario) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $paramsM = [
                $cajaID, $empresaID, $pago['formapagoID'], $usuarioID, $hospedajeID, 
                'INGRESO', 'MOMENTANEO', $monto_pago, "MOMENTÁNEO HAB. " . $habitacion_numero, $descripcion,
                $ahora, $ahora, 'A', $usuarioID
            ];
            $db->ejecutar($sqlM, $paramsM);
        }
    }

    // 3. Actualizar Habitación
    $db->ejecutar("UPDATE habitaciones SET estado = 'MOMENTANEO' WHERE habitacionID = ?", [$habitacionID]);

    $db->commit();
    $_SESSION['mensaje'] = "Registro Momentáneo completado en Habitación " . $habitacion_numero;
    $_SESSION['mensaje_tipo'] = "success";

    $_SESSION['debug_last_op'] = [
        'accion' => 'REGISTRO_MOMENTANEO',
        'habitacionID' => $habitacionID,
        'numero' => $habitacion_numero,
        'estado_en_db' => 'MOMENTANEO',
        'checkout' => $checkout,
        'monto' => $monto_total
    ];

    header("Location: ../habitacioness/habitaciones.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Error Crítico: " . $e->getMessage());
}
