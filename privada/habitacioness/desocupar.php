<?php
session_start();
require_once("../../conexion.php");

// Verificar limpieza de sesión de caja (opcional, pero útil si lo restringen a usuarios con caja)
$empresaID = $_SESSION['empresaID'] ?? 0;

if (isset($_GET['habitacionID'])) {
    $habitacionID = $_GET['habitacionID'];

    try {
        $db->beginTransaction();

        // Buscar hospedaje ACTIVO en esta habitación
        $sql = "SELECT hospedajeID FROM hospedajes WHERE habitacionID = ? AND estado = 'ACTIVO'";
        $hospedaje = $db->obtenerFila($sql, [$habitacionID]);

        if ($hospedaje) {
            // 1. Cerrar el hospedaje: marcar como INACTIVO con fecha de checkout real
            $db->ejecutar(
                "UPDATE hospedajes SET estado = 'INACTIVO', checkout = NOW() WHERE hospedajeID = ?",
                [$hospedaje['hospedajeID']]
            );

            // 2. Habitación → LIMPIEZA
            $db->ejecutar(
                "UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?",
                [$habitacionID]
            );

            $db->commit();
            $_SESSION['message'] = 'La habitación se desocupó con éxito y entró en etapa de LIMPIEZA.';
        } else {
            $db->rollBack();
            $_SESSION['message'] = 'Advertencia: No se encontró hospedaje activo en esta habitación.';
        }

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = 'Error crítico al desocupar: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Petición rechazada: Faltan variables obligatorias.';
}

header("Location: habitaciones.php");
exit();
?>
