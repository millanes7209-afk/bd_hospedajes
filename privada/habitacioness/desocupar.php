<?php
session_start();
require_once("../../conexion.php");

/**
 * DESOCUPAR HABITACIÓN
 * Finaliza el hospedaje (ACTIVO o DEUDA) y pasa la habitación a LIMPIEZA.
 */

$habitacionID = $_REQUEST['habitacionID'] ?? null;

if ($habitacionID) {
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
            // Registrar incidente si se especificó en el modal
            if (isset($_POST['tiene_incidente']) && $_POST['tiene_incidente'] == '1') {
                $descInc = strtoupper(trim($_POST['descripcion'] ?? ''));
                if ($descInc) {
                    // Obtener todos los clientes asociados a este hospedaje
                    $sqlClis = "SELECT clienteID FROM hospedajes_clientes WHERE hospedajeID = ? AND _estado <> 'X'";
                    $clientesAsoc = $db->obtenerTodo($sqlClis, [$hospedaje['hospedajeID']]);
                    foreach ($clientesAsoc as $c) {
                        $sqlInc = "INSERT INTO incidentes (
                                        clienteID, empresaID, descripcion, fecha, estado, 
                                        usuarioID, _usuario, _fec_insercion, _estado
                                    ) VALUES (?, ?, ?, ?, 'PENDIENTE', ?, ?, ?, 'A')";
                        $db->ejecutar($sqlInc, [$c['clienteID'], $empresaID, $descInc, $ahora, $usuarioID, $usuarioID, $ahora]);
                    }
                }
            }

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