<?php
session_start();
require_once("../../conexion.php");

/**
 * PROCESADOR: AÑADIR ACOMPAÑANTES A HOSPEDAJE ACTIVO
 * Solo registra el vínculo en hospedajes_clientes.
 * No afecta montos ni tiempos.
 */

$hospedajeID = $_POST['hospedajeID'] ?? 0;
$clientes = $_POST['clientesSeleccionados'] ?? [];
$habitacion_numero = $_POST['habitacion_numero'] ?? 'S/N';

$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];
$ahora = date("Y-m-d H:i:s");

if (!$hospedajeID || empty($clientes)) {
    echo "Error: Datos incompletos para añadir acompañantes.";
    exit;
}

try {
    // REGLA DE ORO: Validar que el hospedaje pertenece a la empresa y usuario actual (opcional según lógica de negocio, pero recomendado)
    $sqlAudit = "SELECT hospedajeID FROM hospedajes WHERE hospedajeID = ? AND empresaID = ? AND _estado <> 'X'";
    $audit = $db->obtenerFila($sqlAudit, [$hospedajeID, $empresaID]);

    if (!$audit) {
        throw new Exception("Error de Seguridad: No tiene permisos sobre este hospedaje.");
    }

    $db->beginTransaction();

    // Vincular solo los nuevos clientes
    foreach ($clientes as $clienteID) {
        // Verificar si ya está vinculado para evitar duplicados en la tabla pivote
        $sqlCheck = "SELECT hcID FROM hospedajes_clientes WHERE hospedajeID = ? AND clienteID = ? AND _estado <> 'X'";
        $existe = $db->obtenerFila($sqlCheck, [$hospedajeID, $clienteID]);

        if (!$existe) {
            $sqlC = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, 
                                                    _fec_insercion, _fec_modificacion, _estado, _usuario) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
            $db->ejecutar($sqlC, [$empresaID, $hospedajeID, $clienteID, $ahora, $ahora, 'A', $usuarioID]);
        }
    }

    $db->commit();
    $_SESSION['mensaje'] = "Se añadieron los acompañantes correctamente a la Habitación " . $habitacion_numero;
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: ../habitacioness/habitaciones.php");

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error crítico al añadir acompañante: " . $e->getMessage();
    exit;
}
?>
