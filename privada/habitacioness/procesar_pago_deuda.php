<?php
session_start();
require_once("../../conexion.php");

$hospedajeID = $_POST["hospedajeID"] ?? null;
$habitacionID = $_POST["habitacionID"] ?? null;
$monto_deuda = floatval($_POST["monto_total"] ?? 0);
$formaPagoID = $_POST["formaPagoID"] ?? null;

$usuarioID = $_SESSION["sesion_id_usuario"] ?? null;
$empresaID = $_SESSION["empresaID"] ?? null;

if (!$hospedajeID || !$habitacionID) {
    die("Error: Datos incompletos.");
}

// Validar que exista una caja abierta
$sql_caja = "SELECT cajaID FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ? AND empresaID = ?";
$caja = $db->obtenerFila($sql_caja, [$usuarioID, $empresaID]);

if (!$caja) {
    echo "<script>alert('Error: Debe abrir una caja primero.'); window.location.href='habitaciones.php';</script>";
    exit();
}
$cajaID = $caja['cajaID'];

try {
    if (!$db->beginTransaction()) throw new Exception("No se pudo iniciar la transacción.");

    // 1. Agregar el montón de la deuda al valor comercial del hospedaje y marcarlo como cerrado (INACTIVO)
    $sql_update_hospedaje = "UPDATE hospedajes 
                             SET monto = monto + ?, 
                                 checkout = NOW(), 
                                 estado = 'INACTIVO',
                                 _fec_modificacion = NOW()
                             WHERE hospedajeID = ?";
    if ($db->ejecutar($sql_update_hospedaje, [$monto_deuda, $hospedajeID]) === false) {
        throw new Exception("Error al actualizar la cuenta del hospedaje.");
    }

    // 2. Registrar el movimiento de dinero en caja
    if ($monto_deuda > 0) {
        $reg_mov = array(
            "cajaID" => $cajaID,
            "referenciaID" => $hospedajeID,
            "hospedajeID" => $hospedajeID,
            "categoria" => 'HOSPEDAJE',
            "tipo" => 'INGRESO',
            "monto" => $monto_deuda,
            "formapagoID" => $formaPagoID,
            "descripcion" => 'PAGO POR DEUDA VENCIDA - DESOCUPACION',
            "empresaID" => $empresaID,
            "_usuario" => $usuarioID,
            "_fec_insercion" => date("Y-m-d H:i:s"),
            "_estado" => 'A'
        );
        if ($db->AutoExecute("movimientos", $reg_mov, "INSERT") === false) {
            throw new Exception("Error registrando el ingreso del dinero.");
        }
    }

    // 3. Pasar la habitación a estado LIMPIEZA
    $sql_limpieza = "UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?";
    if ($db->ejecutar($sql_limpieza, [$habitacionID]) === false) {
        throw new Exception("Error al liberar la habitación.");
    }

    $db->commit();
    header("Location: habitaciones.php");
    exit();

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("<div style='color:red; font-weight:bold;'>Error crítico: " . $e->getMessage() . "</div>");
}
?>
