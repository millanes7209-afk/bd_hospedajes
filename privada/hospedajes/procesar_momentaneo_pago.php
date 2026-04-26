<?php
session_start();
require_once("../../conexion.php");

// Configurar para capturar errores
header('Content-Type: application/json');

try {
    $db->beginTransaction();

    // 1. Recibir y validar datos
    $hospedajeID = $_POST['hospedajeID'] ?? 0;
    $habitacionID = $_POST['habitacionID'] ?? 0;
    $monto_adicional = floatval($_POST['monto_total'] ?? 0);
    $nueva_salida = $_POST['nueva_salida'] ?? '';
    $tipo_accion = $_POST['tipo_accion'] ?? ''; // 'SALIR' o 'EXTENDER'
    $pagos = $_POST['pagos'] ?? [];

    $usuarioID = $_SESSION['sesion_id_usuario'];
    $empresaID = $_SESSION['empresaID'];
    $cajaID = $_SESSION['caja_abierta_id'] ?? 0;

    if (!$hospedajeID || !$habitacionID || !$tipo_accion) {
        throw new Exception("Datos incompletos para procesar el pago.");
    }

    // 2. Obtener datos actuales del hospedaje
    $sql_h = "SELECT monto, checkout FROM hospedajes WHERE hospedajeID = ?";
    $h_actual = $db->obtenerFila($sql_h, [$hospedajeID]);
    if (!$h_actual) throw new Exception("No se encontró el hospedaje.");

    $nuevo_monto_total = $h_actual['monto'] + $monto_adicional;

    // 3. Registrar los Movimientos de Caja
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        $formaPagoID = $pago['formapagoID'] ?? null;

        if ($monto_pago > 0 && $formaPagoID) {
            $sql_mov = "INSERT INTO movimientos (
                            cajaID, empresaID, formapagoID, usuarioID, 
                            referenciaID, tipo, categoria, monto, 
                            concepto, detalle, _fec_insercion, _estado, _usuario
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'A', ?)";
            
            $concepto = ($tipo_accion === 'SALIR') ? "COBRO DEUDA MOMENTÁNEO" : "EXTENSIÓN MOMENTÁNEO";
            $detalle = "Habitación " . $habitacionID . " - " . ($tipo_accion === 'SALIR' ? "Salida" : "Extensión hasta $nueva_salida");

            $params_mov = [
                $cajaID, $empresaID, $formaPagoID, $usuarioID,
                $hospedajeID, 'INGRESO', 'MOMENTANEO', $monto_pago,
                $concepto, $detalle, $usuarioID
            ];

            $res_mov = $db->ejecutar($sql_mov, $params_mov);
            if (!$res_mov) throw new Exception("Error al registrar el movimiento de pago.");
        }
    }

    // 4. Actualizar el Hospedaje
    $estado_h = ($tipo_accion === 'SALIR') ? 'FINALIZADO' : 'ACTIVO';
    $sql_upd_h = "UPDATE hospedajes SET 
                    monto = ?, 
                    checkout = ?, 
                    estado = ?, 
                    _fec_modificacion = NOW() 
                  WHERE hospedajeID = ?";
    $res_upd_h = $db->ejecutar($sql_upd_h, [$nuevo_monto_total, $nueva_salida, $estado_h, $hospedajeID]);
    if (!$res_upd_h) throw new Exception("Error al actualizar el registro de hospedaje.");

    // 5. Actualizar la Habitación
    $estado_hab = ($tipo_accion === 'SALIR') ? 'LIMPIEZA' : 'MOMENTANEO';
    $sql_upd_hab = "UPDATE habitaciones SET estado = ? WHERE habitacionID = ?";
    $res_upd_hab = $db->ejecutar($sql_upd_hab, [$estado_hab, $habitacionID]);
    if (!$res_upd_hab) throw new Exception("Error al actualizar el estado de la habitación.");

    $db->commit();
    
    // Redirección con éxito
    header("Location: ../habitacioness/habitaciones.php?msg=ok");
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo "<h1>Error Crítico</h1><p>" . $e->getMessage() . "</p>";
    echo "<a href='javascript:history.back()'>Volver atrás</a>";
}
