<?php
session_start();
require_once("../../conexion.php");

// Obtener el usuario actual
$id_usuario = $_SESSION["sesion_id_usuario"];

// Verificar si hay una caja abierta para el usuario actual
$sql_caja_abierta = "SELECT cajaID FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ?";
$rs_caja_abierta = $db->obtenerTodo($sql_caja_abierta, array($id_usuario));

if (count($rs_caja_abierta) > 0) {
    $caja_id = $rs_caja_abierta[0]["cajaID"];

    // Actualizar la caja para cerrarla (Auditando el usuario)
    $sql_cerrar_caja = "UPDATE cajas SET estado = 'CERRADA', fecha_cierre = NOW(), _usuario = ? WHERE cajaID = ?";
    $db->ejecutar($sql_cerrar_caja, array($id_usuario, $caja_id));

    // Establecer un mensaje de éxito en la sesión
    $_SESSION['mensaje'] = 'Caja cerrada exitosamente.';
    $_SESSION['mensaje_tipo'] = 'success';
} else {
    // Si no se encuentra una caja abierta
    $_SESSION['mensaje'] = 'No hay ninguna caja abierta para cerrar.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

// Redirigir de nuevo a la página principal de gestión de habitaciones
header("Location: habitaciones.php");
exit();
?>
