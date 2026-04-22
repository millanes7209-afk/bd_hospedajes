<?php
session_start();
require_once("../../conexion.php");

$db->debug = true; // Puedes descomentar esto para depurar la consulta si es necesario.

// Iniciar HTML para mostrar mensajes de error o confirmación
echo "<html> <head></head> <body>";
// Verificar si se ha recibido al menos un cliente
if (!isset($_POST['clientesSeleccionados']) || count($_POST['clientesSeleccionados']) == 0) {
    echo "<p>Error: Debe agregar al menos un cliente antes de registrar el hospedaje.</p>";
    exit();
}

// Recoger los datos del formulario
$habitacionID = $_POST["habitacionID"];
$reservaID = $_POST["reservaID"];
$formaPagoID = $_POST["formaPagoID"];
$checkout = $_POST["checkout"];
$monto_total = $_POST["monto_total"];
$monto_pendiente = $_POST["monto_pendiente"];
$monto_pagado = $_POST["monto_pagado"];


// Preparar el registro para insertar en la tabla "hospedajes"
$reg = array();
$reg["habitacionID"] = $habitacionID;
$reg["reservaID"] = $reservaID;
$reg["formaPagoID"] = $formaPagoID;
$reg["checkout"] = $checkout;
$reg["monto_total"] = $monto_total;
$reg["monto_pendiente"] = $monto_pendiente;
$reg["tipo"] = 'HOSPEDAJE';
$reg["estado"] = "ACTIVO";

$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Insertar el registro en la tabla "hospedajes"
$rs1 = $db->AutoExecute("hospedajes", $reg, "INSERT");

if ($rs1) {
    // Obtener el hospedajeID generado
    $hospedajeID = $db->Insert_ID();

    // Insertar los registros en la tabla "hospedajes_clientes"
    if (isset($_POST['clientesSeleccionados'])) {
        foreach ($_POST['clientesSeleccionados'] as $clienteID) {

            // Preparar los datos para la tabla "hospedajes_clientes"
            $reg_cliente = array();

            $reg_cliente["hospedajeID"] = $hospedajeID;
            $reg_cliente["clienteID"] = $clienteID;
            $reg_cliente["_fec_insercion"] = date("Y-m-d H:i:s");
            $reg_cliente["_estado"] = 'A';
            $reg_cliente["_usuario"] = $_SESSION["sesion_id_usuario"];
            // Insertar cada cliente asociado al hospedaje
            $rs2 = $db->AutoExecute("hospedajes_clientes", $reg_cliente, "INSERT");

            // Si falla la inserción de algún cliente, mostrar error
            if (!$rs2) {
                echo "<p>Error: No se pudo asociar al cliente con el hospedaje. Por favor, inténtelo de nuevo.</p>";
                exit();
            }
        }
        // Actualizar la reserva asociada
            $sql_update_reserva = "
            UPDATE reservas 
            SET estado = 'CONFIRMADA', 
                estado2 = 'INACTIVO', 
                checkin = ? 
            WHERE reservaID = ?";
        $db->Execute($sql_update_reserva, array($checkin, $reservaID));
            }
    header("Location: ../habitacioness/habitaciones.php");
    exit();
} else {
    // Mostrar un mensaje de error si la inserción falló
    echo "<p>Error: No se pudo registrar el hospedaje. Por favor, inténtelo de nuevo.</p>";
}

// Cerrar HTML
echo "</body></html>";
?>
