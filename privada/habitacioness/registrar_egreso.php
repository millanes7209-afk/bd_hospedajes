<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario

$monto = $_POST['monto'];
$tipo = $_POST['tipo'];
$fecha_pago = date('Y-m-d H:i:s');
$descripcion = $_POST['descripcion'];
$cajaID = $_SESSION['caja_abierta_id'];
$formaPagoID = $_POST["formaPagoID"];
$_fec_insercion = date('Y-m-d H:i:s');
// Insertar el egreso en la tabla 'movimientos_caja'
$reg = array();
//$reg["turnoID"] = $turnoID;
$reg["monto"] = $monto;
$reg["tipo"] = $tipo;
$reg["fecha_pago"] = $fecha_pago;
$reg["descripcion"] = $descripcion;
$reg["cajaID"] = $cajaID;
$reg["formaPagoID"] = $formaPagoID;
$reg["_fec_insercion"] = $_fec_insercion;
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_estado"] = "A";

$db->AutoExecute("egresos", $reg, "INSERT");

// Redirigir con mensaje de éxito
$_SESSION['mensaje'] = "Egreso registrado correctamente.";
$_SESSION['mensaje_tipo'] = "success";
header("Location: habitaciones.php");
exit();
?>
