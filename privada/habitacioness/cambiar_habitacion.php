<?php
session_start();
require_once("../../conexion.php");

// Verificaciones de autenticación o roles si se requiere
$empresaID = $_SESSION['empresaID'] ?? 0;
$usuarioID = $_SESSION['sesion_id_usuario'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospedajeID = $_POST['hospedajeID'] ?? 0;
    $habitacionID_actual = $_POST['habitacionID_actual'] ?? 0;
    $nueva_habitacionID = $_POST['nueva_habitacionID'] ?? 0;

    if ($hospedajeID && $habitacionID_actual && $nueva_habitacionID) {
        try {
            $db->beginTransaction();

            // 1. Verificar si la nueva habitación sigue disponible (lock de lectura)
            $sqlDisp = "SELECT estado FROM habitaciones WHERE habitacionID = ?";
            $habNueva = $db->obtenerFila($sqlDisp, [$nueva_habitacionID]);

            if ($habNueva && $habNueva['estado'] === 'DISPONIBLE') {
                
                // 2. Mover el hospedaje a la nueva habitación
                $db->ejecutar(
                    "UPDATE hospedajes SET habitacionID = ? WHERE hospedajeID = ? AND estado = 'ACTIVO'",
                    [$nueva_habitacionID, $hospedajeID]
                );

                // 3. Habitación antigua → LIMPIEZA
                $db->ejecutar(
                    "UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ?",
                    [$habitacionID_actual]
                );

                // 4. Habitación nueva → OCUPADA
                $db->ejecutar(
                    "UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?",
                    [$nueva_habitacionID]
                );

                $db->commit();
                $_SESSION['message'] = "¡Cambio de habitación exitoso! Cliente reubicado.";
            } else {
                $db->rollBack();
                $_SESSION['error'] = "Operación cancelada: La habitación destino ya no está DISPONIBLE.";
            }

        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Error durante el cambio: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Datos insuficientes para procesar el cambio de cuarto.";
    }
} else {
    $_SESSION['error'] = "Método de envío inválido.";
}

header("Location: habitaciones.php");
exit();
?>
