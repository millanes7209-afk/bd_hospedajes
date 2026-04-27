<?php
session_start();
require_once("../../conexion.php");

$hospedajeID  = $_POST["hospedajeID"]  ?? null;
$habitacionID = $_POST["habitacionID"] ?? null;
$monto_deuda  = floatval($_POST["monto_total"] ?? 0);
$formaPagoID  = $_POST["formaPagoID"]  ?? null;

$usuarioID  = $_SESSION["sesion_id_usuario"] ?? null;
$empresaID  = $_SESSION["empresaID"]         ?? null;
$ahora      = date("Y-m-d H:i:s");

if (!$hospedajeID || !$habitacionID || !$formaPagoID) {
    $_SESSION['mensaje']      = "Error: Datos incompletos. Debe seleccionar una forma de pago.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}

// Validar que exista una caja abierta
$caja = $db->obtenerFila(
    "SELECT cajaID FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ? AND empresaID = ? AND _estado <> 'X'",
    [$usuarioID, $empresaID]
);

if (!$caja) {
    $_SESSION['mensaje']      = "Error: Debe tener una caja abierta para registrar pagos.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}
$cajaID = $caja['cajaID'];

try {
    if (!$db->beginTransaction()) throw new Exception("No se pudo iniciar la transacción.");

    // 1. Marcar el hospedaje como INACTIVO y actualizar checkout
    $sql_hospedaje = "UPDATE hospedajes 
                      SET estado = 'INACTIVO',
                          checkout = ?,
                          _fec_modificacion = ?
                      WHERE hospedajeID = ?";
    if ($db->ejecutar($sql_hospedaje, [$ahora, $ahora, $hospedajeID]) === false) {
        throw new Exception("Error al cerrar el hospedaje.");
    }

    // 2. Registrar el movimiento financiero (solo si hay monto)
    if ($monto_deuda > 0) {
        $sql_mov = "INSERT INTO movimientos 
                        (cajaID, empresaID, formapagoID, usuarioID, recaudacionID, referenciaID,
                         tipo, categoria, monto, concepto, detalle, entregado,
                         _fec_insercion, _fec_modificacion, _estado, _usuario)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params_mov = [
            $cajaID, $empresaID, $formaPagoID, $usuarioID, null, $hospedajeID,
            'INGRESO', 'HOSPEDAJE', $monto_deuda,
            'PAGO DE DEUDA - DESOCUPACION', 'Pago acordado al momento de salida',
            0, $ahora, $ahora, 'A', $usuarioID
        ];
        if ($db->ejecutar($sql_mov, $params_mov) === false) {
            throw new Exception("Error al registrar el movimiento de pago.");
        }
    }

    // 3. Pasar la habitación a estado LIMPIEZA
    $sql_limpieza = "UPDATE habitaciones SET estado = 'LIMPIEZA', _fec_modificacion = ? WHERE habitacionID = ?";
    if ($db->ejecutar($sql_limpieza, [$ahora, $habitacionID]) === false) {
        throw new Exception("Error al liberar la habitación.");
    }

    $db->commit();

    $_SESSION['mensaje']      = "Pago registrado. Habitación pasada a LIMPIEZA.";
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: habitaciones.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("<div style='color:red; font-weight:bold; padding:20px;'>Error crítico: " . $e->getMessage() . "</div>");
}
?>

