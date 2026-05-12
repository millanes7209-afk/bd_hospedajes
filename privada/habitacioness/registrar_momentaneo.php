<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$descripcion = strtoupper($_POST['descripcion']);
$habitacionID = $_POST["habitacionID"];
$monto = $_POST['monto'];
$cajaID = $_SESSION['caja_abierta_id'];
$formaPagoID = $_POST["formaPagoID"];
$empresaID = $_SESSION['empresaID'];
$usuarioID = $_SESSION["sesion_id_usuario"];
$fecha_ahora = date('Y-m-d H:i:s');

try {
    $db->beginTransaction();

    // 1. Insertar el ingreso por momentáneo
    $sql_ingreso = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, forma_pago, _fec_insercion, _usuario, _estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')";
    
    // Usamos una cuenta predeterminada para momentáneos o una genérica
    $cuentaID = 1; // Asumimos 1 como Ingreso General si no se especifica

    $params_ingreso = [
        $empresaID, $cajaID, $cuentaID, $usuarioID, $monto, 
        "INGRESO MOMENTANEO: $descripcion", $fecha_ahora, $formaPagoID, $fecha_ahora, $usuarioID
    ];

    $db->ejecutar($sql_ingreso, $params_ingreso);

    // 2. Cambiar el estado de la habitación a 'MOMENTANEO'
    $db->ejecutar("UPDATE habitaciones SET estado = 'MOMENTANEO' WHERE habitacionID = ?", [$habitacionID]);

    $db->commit();

    $_SESSION['mensaje'] = "Registro de momentáneo guardado correctamente.";
    $_SESSION['mensaje_tipo'] = "success";

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    $_SESSION['mensaje'] = "Error al registrar: " . $e->getMessage();
    $_SESSION['mensaje_tipo'] = "danger";
}

header("Location: habitaciones.php");
exit();
?>
