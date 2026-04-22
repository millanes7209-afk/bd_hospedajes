<?php
session_start();
require_once("../../conexion.php");

// Validar que exista una caja abierta
if (!isset($_SESSION['caja_abierta_id']) || empty($_SESSION['caja_abierta_id'])) {
    $_SESSION['mensaje'] = "Error: No hay una caja abierta para este usuario.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: habitaciones.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresaID = $_SESSION['empresaID'];
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $cajaID = $_SESSION['caja_abierta_id'];
    
    $monto = (float)$_POST['monto'];
    $tipo_mov = $_POST['tipo_movimiento']; // INGRESO o EGRESO
    $tipo_categoria = $_POST['tipo']; // Ej: BAÑO, MANTENIMIENTO, etc.
    $descripcion = strtoupper(trim($_POST['descripcion']));
    $formaPagoID = (int)$_POST['formaPagoID'];
    
    // Iniciar Transacción
    $db->ejecutar("START TRANSACTION");

    try {
        $referenciaID = null;
        $categoria_master = ($tipo_mov === 'INGRESO') ? 'SERVICIO_EXTRA' : 'GASTO';

        // 1. REGISTRO EN TABLAS DE DETALLE
        if ($tipo_mov === 'INGRESO') {
            // Mapear tipos a la tabla servicios_extra (ENUM: VISITA, MOMENTANEO, BANO)
            $tipo_se = null;
            if ($tipo_categoria === 'BANO') $tipo_se = 'BANO';
            elseif ($tipo_categoria === 'VISITA') $tipo_se = 'VISITA';
            elseif ($tipo_categoria === 'MOMENTANEO') $tipo_se = 'MOMENTANEO';
            elseif ($tipo_categoria === 'OTRO') $tipo_se = 'VISITA'; 

            if ($tipo_se) {
                $sql_se = "INSERT INTO servicios_extra (empresaID, tipo, descripcion, monto, fecha, _fec_insercion, _usuario, _estado) 
                           VALUES (?, ?, ?, ?, NOW(), NOW(), ?, 'A')";
                $db->ejecutar($sql_se, [$empresaID, $tipo_se, $descripcion, $monto, $usuarioID]);
                $referenciaID = $db->ultimoInsertId();
            }
        } else {
            // Mapear tipos a la tabla gastos (ENUM: MANTENIMIENTO, INSUMOS, OTRO)
            $tipo_ga = 'OTRO';
            if ($tipo_categoria === 'MANTENIMIENTO') $tipo_ga = 'MANTENIMIENTO';
            elseif ($tipo_categoria === 'INSUMOS' || $tipo_categoria === 'LIMPIEZA') $tipo_ga = 'INSUMOS';

            $sql_ga = "INSERT INTO gastos (empresaID, tipo, descripcion, monto, fecha, _fec_insercion, _usuario, _estado) 
                       VALUES (?, ?, ?, ?, NOW(), NOW(), ?, 'A')";
            $db->ejecutar($sql_ga, [$empresaID, $tipo_ga, $descripcion, $monto, $usuarioID]);
            $referenciaID = $db->ultimoInsertId();
        }

        // 2. REGISTRO MAESTRO EN MOVIMIENTOS
        $sql_mov = "INSERT INTO movimientos (empresaID, cajaID, formaPagoID, monto, categoria, concepto, detalle, referenciaID, usuarioID, tipo, _fec_insercion, _usuario, _estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'A')";
        
        $concepto = $tipo_mov . ": " . $tipo_categoria;
        $db->ejecutar($sql_mov, [
            $empresaID, $cajaID, $formaPagoID, $monto, $categoria_master, $concepto, $descripcion, $referenciaID, $usuarioID, $tipo_mov, $usuarioID
        ]);

        $db->ejecutar("COMMIT");
        $_SESSION['mensaje'] = "Movimiento de $tipo_mov registrado correctamente.";
        $_SESSION['mensaje_tipo'] = "success";

    } catch (Exception $e) {
        $db->ejecutar("ROLLBACK");
        $_SESSION['mensaje'] = "Error al procesar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: habitaciones.php");
    exit();
}
?>
