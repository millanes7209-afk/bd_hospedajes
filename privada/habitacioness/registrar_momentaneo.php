<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$descripcion = $_POST['descripcion'];
$tipo = $_POST['tipo'];
$habitacionID = $_POST["habitacionID"];
$monto = $_POST['monto'];
$cajaID = $_SESSION['caja_abierta_id'];
$formaPagoID = $_POST["formaPagoID"];
$fecha_pago = date('Y-m-d H:i:s');

// Insertar el egreso en la tabla 'movimientos_caja'
$reg = array();
//$reg["turnoID"] = $turnoID;
$reg["descripcion"] = $descripcion;
$reg["monto"] = $monto;
$reg["tipo"] = $tipo;
$reg["formaPagoID"] = $formaPagoID;
$reg["cajaID"] = $cajaID;
$reg["fecha_pago"] = $fecha_pago;

$reg["_fec_insercion"] = $fecha_pago;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_estado"] = "A";

$db->AutoExecute("ingresos", $reg, "INSERT");



// Cambiar el estado de la habitación a 'MOMENTANEO'
$updateHabitacion = array();
$updateHabitacion["estado"] = 'MOMENTANEO';

$db->AutoExecute("habitaciones", $updateHabitacion, "UPDATE", "habitacionID='" . $habitacionID . "'");


// Redirigir con mensaje de éxito
$_SESSION['mensaje'] = "Ingreso registrado correctamente.";
$_SESSION['mensaje_tipo'] = "success";
header("Location: habitaciones.php");
exit();
?>
