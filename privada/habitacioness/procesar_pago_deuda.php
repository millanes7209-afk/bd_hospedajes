<?php
session_start();
require_once("../../conexion.php");


$hospedajeID_anterior = $_POST["hospedajeID"];
$habitacionID = $_POST["habitacionID"];
$monto_pendiente = $_POST["monto_total"];
$monto_total = $_POST["monto_total"];
$numero = $_POST["habitacion_numero"];
$formaPagoID = $_POST["formaPagoID"];
$checkout = date("Y-m-d H:i:s"); //

// Actualizar el estado del hospedaje anterior a 'INACTIVO'
$updateData = array();
$updateData["estado"] = 'INACTIVO';
$reg["checkout"] = $checkout;
$rs1 = $db->AutoExecute("hospedajes", $updateData, "UPDATE", "hospedajeID='" . $hospedajeID_anterior . "'");

// Crear un arreglo con los datos para el nuevo hospedaje
$reg = array();
$reg["habitacionID"] = $habitacionID;
$reg["checkout"] = $checkout;
$reg["monto_pendiente"] = $monto_pendiente;
$reg["monto_total"] = $monto_total;
$reg["tipo"] = 'HOSPEDAJE';
$reg["formaPagoID"] = $formaPagoID;
$reg["estado"] = 'INACTIVO';
$reg["hospedaje_anteriorID"] = $hospedajeID_anterior; //

$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A'; // Valor por defecto


$db->AutoExecute("hospedajes", $reg, "INSERT");
// Obtener el ID del hospedaje recién generado
$hospedajeID = $db->Insert_ID();
        if ($monto_pendiente > 0) {
        // Validar si hay una caja abierta
        $cajaID = $_SESSION['caja_abierta_id'];
        if (!$cajaID) {
            echo "<p>Error: No hay una caja abierta. Por favor, abre una caja antes de registrar el hospedaje.</p>";
            exit();
        }

        // Preparar los datos del ingreso
        $reg_ingreso = array();
        $reg_ingreso["_fec_insercion"] = date("Y-m-d H:i:s");
        $reg_ingreso["_usuario"] = $_SESSION["sesion_id_usuario"];
        $reg_ingreso["_estado"] = 'A';
        $reg_ingreso["monto"] = $monto_pendiente;
        $reg_ingreso["formaPagoID"] = $formaPagoID;
        $reg_ingreso["fecha_pago"] = date("Y-m-d H:i:s");
        $reg_ingreso["tipo"] = "HOSPEDAJE";
        $reg_ingreso["descripcion"] = $numero;
        $reg_ingreso["hospedajeID"] = $hospedajeID;
        $reg_ingreso["cajaID"] = $cajaID;

        // Insertar el ingreso en la tabla 'ingresos'
        $rs3 = $db->AutoExecute("ingresos", $reg_ingreso, "INSERT");

        // Verificar si la inserción fue exitosa
        if (!$rs3) {
            echo "<p>Error: No se pudo registrar el ingreso asociado. Por favor, inténtelo de nuevo.</p>";
            exit();
        }

    }

// Cambiar el estado de la habitación a 'LIMPIEZA'
$updateHabitacion = array();
$updateHabitacion["estado"] = 'LIMPIEZA';
$updateHabitacion["_usuario"] = $_SESSION["sesion_id_usuario"];

$db->AutoExecute("habitaciones", $updateHabitacion, "UPDATE", "habitacionID='" . $habitacionID . "'");

// Redirigir a la página de habitaciones
header("Location: habitaciones.php");
exit();
?>  
