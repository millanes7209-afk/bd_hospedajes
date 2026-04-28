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

    // 2. REGISTRO CONTABLE (ingresos + ingreso_pagos)
    $cuenta = $db->obtenerFila("SELECT cuentaID FROM cuentas WHERE codigo = '402' AND empresaID = ?", [$empresaID]);
    if (!$cuenta) throw new Exception("Error Contable: No se encontró la cuenta [402] para esta empresa.");
    $cuentaID = $cuenta['cuentaID'];

    // Determinar concepto y estado según la acción
    $concepto = ($tipo_accion === 'SALIR') ? "COBRO EXTRA SALIDA MOMENTÁNEO" : "EXTENSIÓN MOMENTÁNEO (+1 Hora)";
    $estado_h = ($tipo_accion === 'SALIR') ? 'FINALIZADO' : 'ACTIVO';
    $ahora = date("Y-m-d H:i:s");

    // 5. INSERTAR EN LA SUPER-TABLA INGRESOS
    $sqlI = "INSERT INTO ingresos (empresaID, cajaID, cuentaID, usuarioID, monto_total, concepto, fecha, _usuario) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsI = [$empresaID, $cajaID, $cuentaID, $usuarioID, $monto_adicional, $concepto . " HAB. " . $habitacionID, $ahora, $usuarioID];
    
    if ($db->ejecutar($sqlI, $paramsI) === false) throw new Exception("Error al registrar el ingreso maestro.");
    $ingresoID = $db->lastInsertId();

    // 6. DETALLE DE PAGOS
    foreach ($pagos as $pago) {
        $monto_pago = floatval(str_replace(',', '.', $pago['monto']));
        $formaPagoID = $pago['formapagoID'] ?? null;
        if ($monto_pago > 0 && $formaPagoID) {
            $sqlIP = "INSERT INTO ingreso_pagos (ingresoID, formapagoID, monto) VALUES (?, ?, ?)";
            if ($db->ejecutar($sqlIP, [$ingresoID, $formaPagoID, $monto_pago]) === false) {
                throw new Exception("Error al registrar el desglose del pago.");
            }
        }
    }

    // 7. ACTUALIZAR HOSPEDAJE ANTERIOR (INACTIVAR)
    $db->ejecutar("UPDATE hospedajes SET estado = 'INACTIVO' WHERE hospedajeID = ?", [$hospedajeID]);

    // 8. CREAR NUEVO HOSPEDAJE (Técnico para el pago)
    $sqlNewH = "INSERT INTO hospedajes (empresaID, habitacionID, cajaID, ingresoID, checkin, checkout, monto, estado, observaciones, 
                                      _fec_insercion, _fec_modificacion, _estado, _usuario) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $paramsNewH = [
        $empresaID, $habitacionID, $cajaID, $ingresoID, $ahora, $nueva_salida, $monto_adicional, 
        $estado_h, $concepto, $ahora, $ahora, 'A', $usuarioID
    ];
    
    if ($db->ejecutar($sqlNewH, $paramsNewH) === false) {
        throw new Exception("Error al registrar el nuevo registro de hospedaje vinculado al pago.");
    }

    // 9. Actualizar la Habitación
    $estado_hab = ($tipo_accion === 'SALIR') ? 'LIMPIEZA' : 'OCUPADA'; // Cambiado de MOMENTANEO a OCUPADA para consistencia
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
