<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$habitacionID = $_POST["habitacionID"] ?? null;
$clienteID = $_POST["clienteID"] ?? null;
$reservaID = $_POST["reservaID"] ?? null;
$monto_total = $_POST["monto_total"] ?? null;
$checkout = $_POST["checkout"] ?? null;
$monto_pagado = $_POST["monto_pagado"] ?? null;
$monto_pendiente = $_POST["monto_pendiente"] ?? null;
$formaPagoID = $_POST["formaPagoID"] ?? null;

// Obtener el ID del usuario desde la sesión
$_usuario = $_SESSION["sesion_id_usuario"];

$missingFields = [];

// Verificar si faltan datos
if ($habitacionID === null || $habitacionID === '') $missingFields[] = 'Habitación ID';
if ($clienteID === null || $clienteID === '') $missingFields[] = 'Cliente ID';
if ($reservaID === null || $reservaID === '') $missingFields[] = 'Reserva ID';
if ($monto_total === null || $monto_total === '') $missingFields[] = 'Monto Total';
if ($checkout === null || $checkout === '') $missingFields[] = 'Checkout';
if ($monto_pagado === null || $monto_pagado === '') $missingFields[] = 'Monto Pagado';
if ($monto_pendiente === null || $monto_pendiente === '') $missingFields[] = 'Monto Pendiente';
if ($formaPagoID === null || $formaPagoID === '') $missingFields[] = 'Forma de pago';

// Si hay campos faltantes, mostrar mensaje y detener la ejecución
if (!empty($missingFields)) {
    $fields = implode(', ', $missingFields);
    echo "<html><body>";
    echo "<p style='color:red;'>Faltan los siguientes campos: $fields</p>";
    echo "<a href='javascript:history.back()'>Volver</a>";
    echo "</body></html>";
    exit();
}

// Crear un arreglo con los campos a insertar en 'hospedajes'
$reg = array();
$reg["habitacionID"] = $habitacionID;
$reg["clienteID"] = $clienteID;
$reg["monto_total"] = $monto_total;
$reg["checkout"] = $checkout;
$reg["monto_pagado"] = $monto_pagado;
$reg["monto_pendiente"] = $monto_pendiente;
$reg["formaPagoID"] = $formaPagoID;
$reg["reservaID"] = $reservaID;
$reg["estado"] = 'ACTIVO';
$reg["_usuario"] = $_usuario;
$reg["_estado"] = 'A';

// Intentar insertar el nuevo hospedaje
$rs1 = $db->AutoExecute("hospedajes", $reg, "INSERT");

// Verificar si la inserción fue exitosa
if (!$rs1) {
    $error = $db->ErrorMsg(); // Obtener mensaje de error
    $data = json_encode($reg); // Codificar los datos intentados
    echo "<html><body>";
    echo "<p style='color:red;'>Error al registrar el hospedaje. Detalle: $error. Datos intentados: $data</p>";
    echo "<a href='javascript:history.back()'>Volver</a>";
    echo "</body></html>";
    exit();
}

// Actualizar el estado de la habitación a 'OCUPADA'
$reg_hab = array();
$reg_hab["estado"] = 'OCUPADA';
$reg_hab["_usuario"] = $_usuario;

$rs2 = $db->AutoExecute("habitaciones", $reg_hab, "UPDATE", "habitacionID='" . $habitacionID . "'");

// Verificar si la actualización fue exitosa
if (!$rs2) {
    echo "<html><body>";
    echo "<p style='color:red;'>Error al actualizar el estado de la habitación.</p>";
    echo "<a href='javascript:history.back()'>Volver</a>";
    echo "</body></html>";
    exit();
}

// Actualizar el estado de la reserva a 'CONFIRMADA' y 'INACTIVO'
$reg_reserva = array();
$reg_reserva["estado"] = 'CONFIRMADA';
$reg_reserva["estado2"] = 'INACTIVO';
$reg_reserva["_usuario"] = $_usuario;

$rs3 = $db->AutoExecute("reservas", $reg_reserva, "UPDATE", "reservaID='" . $reservaID . "'");

// Verificar si la actualización fue exitosa
if (!$rs3) {
    echo "<html><body>";
    echo "<p style='color:red;'>Error al actualizar el estado de la reserva.</p>";
    echo "<a href='javascript:history.back()'>Volver</a>";
    echo "</body></html>";
    exit();
}

// Redirigir de vuelta a la lista de habitaciones
echo "<html><body>";

header("Location: habitaciones.php");
echo "</body></html>";
exit();
?>
