<?php
session_start();
require_once("../../conexion.php");

// Capturar errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$habitacionID = $_POST["habitacionID"];
$formaPagoID = $_POST["formaPagoID"];
$checkin = $_POST["checkin"];
$clienteID = $_POST["clienteID"];
$monto_reserva = $_POST["monto_reserva"];
$monto_pagado = $_POST["monto_pagado"];
$monto_pendiente = $_POST["monto_pendiente"];

$reg = array();
$reg["habitacionID"] = $habitacionID;
$reg["formaPagoID"] = $formaPagoID;
$reg["checkin"] = $checkin;
$reg["clienteID"] = $clienteID;
$reg["monto_reserva"] = $monto_reserva;
$reg["monto_pagado"] = $monto_pagado;
$reg["monto_pendiente"] = $monto_pendiente;

$reg["fecha_reserva"] = date("Y-m-d H:i:s");
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Imprimir el array de datos para verificar
echo "<pre>";
print_r($reg);
echo "</pre>";

// Intentar insertar en la base de datos
try {
    $rs1 = $db->AutoExecute("reservas", $reg, "INSERT");
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

header("Location: ../habitacioness/habitaciones.php");
exit();
?>
