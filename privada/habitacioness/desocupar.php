<?php
session_start();
require_once("../../conexion.php");

/**
 * DESOCUPAR HABITACIÓN
 * Finaliza el hospedaje (ACTIVO o DEUDA) y pasa la habitación a LIMPIEZA.
 */

if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];
    $usuarioID = $_SESSION["sesion_id_usuario"];
    $empresaID = $_SESSION["empresaID"];

    try {
        $ahora = date('Y-m-d H:i:s');
        $db->beginTransaction();

        // 1. Buscar el hospedaje activo o en deuda EN ESTA EMPRESA
        $sql = "SELECT hospedajeID FROM hospedajes 
                WHERE habitacionID = ? AND empresaID = ? AND estado IN ('ACTIVO', 'DEUDA') AND _estado <> 'X'
                ORDER BY hospedajeID DESC LIMIT 1";
        $hospedaje = $db->obtenerFila($sql, [$habitacionID, $empresaID]);

        if ($hospedaje) {
            // 2. Finalizar el hospedaje (Estado unificado: INACTIVO)
            $db->ejecutar("UPDATE hospedajes SET estado = 'INACTIVO', checkout = ?, _fec_modificacion = ?, _usuario = ? 
                          WHERE hospedajeID = ? AND empresaID = ?", [$ahora, $ahora, $usuarioID, $hospedaje['hospedajeID'], $empresaID]);

            // 3. Cambiar habitación a LIMPIEZA (Base de datos real)
            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacionID, $empresaID]);

            $_SESSION['mensaje'] = "Habitación desocupada con éxito. Estado: LIMPIEZA.";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            // Limpieza de seguridad por si la habitación quedó trabada visualmente
            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacionID, $empresaID]);
            $_SESSION['mensaje'] = "La habitación fue reseteada a LIMPIEZA.";
            $_SESSION['mensaje_tipo'] = "warning";
        }

        $db->commit();
    } catch (Exception $e) {
        if ($db->inTransaction())
            $db->rollBack();
        $_SESSION['mensaje'] = "Error al desocupar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }
}

header("Location: habitaciones.php");
exit();
?>