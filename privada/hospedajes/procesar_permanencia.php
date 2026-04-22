<?php
session_start();
require_once("../../conexion.php");

/**
 * MOTOR DE PROCESAMIENTO DE PERMANENCIA
 * Finaliza estancia previa y genera una nueva con continuidad de huéspedes.
 */

$hospedajeID_anterior = $_POST['hospedajeID_anterior'];
$habitacionID = $_POST['habitacionID'];
$checkout = $_POST['checkout'];
$monto_total = $_POST['monto_total'];
$descripcion = $_POST['descripcion'];
$habitacion_numero = $_POST['habitacion_numero'];

$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];
$cajaID = $_SESSION['caja_abierta_id'];
$ahora = date("Y-m-d H:i:s");

// Listas de Clientes y Pagos
$clientes = $_POST['clientesSeleccionados']; // Incluye los viejos y los nuevos añadidos
$pagos = $_POST['pagos'];

if (!$hospedajeID_anterior || !$habitacionID || empty($clientes) || !$cajaID) {
    echo "Error: Datos incompletos para procesar la permanencia.";
    exit;
}

try {
    // REGLA DE ORO: Validar auditoría antes de cualquier cambio financiero
    $sqlAudit = "SELECT hospedajeID FROM hospedajes 
                 WHERE hospedajeID = ? AND empresaID = ? AND estado = 'ACTIVO' AND _estado <> 'X'";
    $audit = $db->obtenerFila($sqlAudit, [$hospedajeID_anterior, $empresaID]);

    if (!$audit) {
        throw new Exception("Error de Seguridad: No tiene permisos o el hospedaje no está activo.");
    }

    $db->beginTransaction();

    // 1. FINALIZAR HOSPEDAJE ANTERIOR
    $sqlOld = "UPDATE hospedajes SET estado = 'INACTIVO', observaciones = CONCAT(observaciones, ' | EXTENDIDO POR PERMANENCIA') 
               WHERE hospedajeID = ?";
    $db->ejecutar($sqlOld, [$hospedajeID_anterior]);

    // 2. CREAR NUEVO HOSPEDAJE (LA PERMANENCIA)
    $sqlNew = "INSERT INTO hospedajes (empresaID, habitacionID, checkin, checkout, monto, estado, observaciones, 
                                     _fec_insercion, _fec_modificacion, _estado, _usuario) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsNew = [
        $empresaID, $habitacionID, $ahora, $checkout, $monto_total, 
        'ACTIVO', $descripcion, $ahora, $ahora, 'A', $usuarioID
    ];
    $db->ejecutar($sqlNew, $paramsNew);
    $nuevoHospedajeID = $db->lastInsertId();

    // 3. VINCULAR TODOS LOS CLIENTES AL NUEVO REGISTRO
    foreach ($clientes as $clienteID) {
        $sqlC = "INSERT INTO hospedajes_clientes (empresaID, hospedajeID, clienteID, 
                                                _fec_insercion, _fec_modificacion, _estado, _usuario) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $db->ejecutar($sqlC, [$empresaID, $nuevoHospedajeID, $clienteID, $ahora, $ahora, 'A', $usuarioID]);
    }

    // 4. REGISTRAR LOS NUEVOS PAGOS
    foreach ($pagos as $pago) {
            $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
            if ($monto_pago > 0) {
                // ESTRUCTURA DE 15 COLUMNAS PARA COINCIDIR CON registrar_hospedaje.php
                $sqlM = "INSERT INTO movimientos (cajaID, empresaID, formapagoID, usuarioID, recaudacionID, referenciaID, 
                                                tipo, categoria, monto, concepto, entregado, 
                                                _fec_insercion, _fec_modificacion, _estado, _usuario) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $paramsM = [
                    $cajaID, $empresaID, $pago['formaPagoID'], $usuarioID, null, $nuevoHospedajeID, 
                    'INGRESO', 'HOSPEDAJE', $monto_pago, "HOSPEDAJE (EXTENSIÓN) HAB. " . $habitacion_numero, 0,
                    $ahora, $ahora, 'A', $usuarioID
                ];
                $db->ejecutar($sqlM, $paramsM);
            }
    }

    $db->commit();
    $_SESSION['mensaje'] = "Permanencia registrada correctamente en Habitación " . $habitacion_numero;
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: ../habitacioness/habitaciones.php");

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "Error crítico: " . $e->getMessage();
    exit;
}
?>
