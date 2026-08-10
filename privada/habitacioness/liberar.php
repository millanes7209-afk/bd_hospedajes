<?php
session_start();
require_once("../../conexion.php");

/**
 * LIBERAR HABITACIÓN (Cancelación de Reserva o similar)
 */

if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];
    $usuarioID = $_SESSION["sesion_id_usuario"];
    $empresaID = $_SESSION["empresaID"];

    try {
        $ahora = date('Y-m-d H:i:s');
        $db->beginTransaction();

        // Buscar hospedaje/reserva vinculado
        $sql = "SELECT hospedajeID FROM hospedajes 
                WHERE habitacionID = ? AND empresaID = ? AND estado IN ('ACTIVO', 'DEUDA', 'RESERVADA') AND _estado <> 'X'
                ORDER BY hospedajeID DESC LIMIT 1";
        $hospedaje = $db->obtenerFila($sql, [$habitacionID, $empresaID]);

        if ($hospedaje) {
            $db->ejecutar("UPDATE hospedajes SET estado = 'INACTIVO', _fec_modificacion = ?, _usuario = ? 
                          WHERE hospedajeID = ? AND empresaID = ?", [$ahora, $usuarioID, $hospedaje['hospedajeID'], $empresaID]);

            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacionID, $empresaID]);
            $_SESSION['mensaje'] = "Habitación liberada (LIMPIEZA).";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            // Si estaba reservada y no hay hospedaje, solo limpiar habitación
            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacionID, $empresaID]);
            $_SESSION['mensaje'] = "Habitación puesta en LIMPIEZA.";
            $_SESSION['mensaje_tipo'] = "info";
        }

        $db->commit();
    } catch (Exception $e) {
        if ($db->inTransaction())
            $db->rollBack();
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }
}

header("Location: habitaciones.php");
exit();
?>