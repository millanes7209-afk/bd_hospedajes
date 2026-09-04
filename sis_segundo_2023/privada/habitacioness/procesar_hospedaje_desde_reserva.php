<?php
session_start();
require_once("../../conexion.php");

// Obtener datos
$habitacionID = $_POST["habitacionID"] ?? null;
$clienteID = $_POST["clienteID"] ?? null;
$reservaID = $_POST["reservaID"] ?? null;
$monto_total = $_POST["monto_total"] ?? null;
$checkout = $_POST["checkout"] ?? null;
$monto_pagado = $_POST["monto_pagado"] ?? null;
$monto_pendiente = $_POST["monto_pendiente"] ?? null;
$formaPagoID = $_POST["formaPagoID"] ?? null;

$empresaID = $_SESSION['empresaID'];
$usuarioID = $_SESSION["sesion_id_usuario"];
$ahora = date("Y-m-d H:i:s");

if (!$habitacionID || !$reservaID) {
    die("Error: Faltan datos críticos.");
}

try {
    $db->beginTransaction();

    // 1. Insertar el nuevo hospedaje
    $sql_hospedaje = "INSERT INTO hospedajes (empresaID, habitacionID, monto, checkout, monto_total, monto_pagado, monto_pendiente, formaPagoID, reservaID, estado, _fec_insercion, _usuario, _estado) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVO', ?, ?, 'A')";
    
    $params_hospedaje = [
        $empresaID, $habitacionID, $monto_total, $checkout, $monto_total, 
        $monto_pagado, $monto_pendiente, $formaPagoID, $reservaID, $ahora, $usuarioID
    ];
    
    $db->ejecutar($sql_hospedaje, $params_hospedaje);
    $hospedajeID = $db->ultimoID();

    // 2. Vincular Cliente al Hospedaje
    $sql_hc = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, _fec_insercion, _usuario, _estado) 
               VALUES (?, ?, ?, ?, ?, 'A')";
    $db->ejecutar($sql_hc, [$empresaID, $hospedajeID, $clienteID, $ahora, $usuarioID]);

    // 3. Actualizar Habitación a OCUPADA
    $db->ejecutar("UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?", [$habitacionID]);

    // 4. Finalizar Reserva
    $db->ejecutar("UPDATE reservas SET estado = 'CONFIRMADA', estado2 = 'INACTIVO' WHERE reservaID = ?", [$reservaID]);

    $db->commit();
    $_SESSION['mensaje'] = "Hospedaje iniciado correctamente desde la reserva.";
    $_SESSION['mensaje_tipo'] = "success";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "danger";
}

header("Location: habitaciones.php");
exit();
?>
