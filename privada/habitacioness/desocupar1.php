<?php
session_start();
require_once("../../conexion.php");

/**
 * DESOCUPAR HABITACIÓN MOMENTÁNEA
 * Las habitaciones MOMENTÁNEO no generan un registro en la tabla hospedajes,
 * solo se registra el ingreso en caja. Por eso aquí solo cambiamos el estado
 * de la habitación a LIMPIEZA.
 */

if (isset($_GET['habitacionID'])) {
    $habitacionID = (int) $_GET['habitacionID'];
    $empresaID    = $_SESSION['empresaID'];

    try {
        $db->beginTransaction();

        // Verificar que la habitación esté en estado MOMENTANEO (seguridad)
        $hab = $db->obtenerFila(
            "SELECT estado FROM habitaciones WHERE habitacionID = ? AND empresaID = ?",
            [$habitacionID, $empresaID]
        );

        if (!$hab) {
            throw new Exception("Habitación no encontrada o no pertenece a esta empresa.");
        }

        // Poner la habitación en LIMPIEZA
        $db->ejecutar(
            "UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?",
            [$habitacionID, $empresaID]
        );

        $db->commit();

        $_SESSION['mensaje']      = "Habitación liberada. Estado: LIMPIEZA.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        $_SESSION['mensaje']      = "Error al liberar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }
}

header("Location: habitaciones.php");
exit();
?>
