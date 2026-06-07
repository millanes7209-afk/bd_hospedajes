<?php
session_start();
require_once("../../conexion.php");

// Obtener el usuario actual
$id_usuario = $_SESSION["sesion_id_usuario"];

// Verificar si hay una caja abierta para el usuario actual
$sql_caja_abierta = "SELECT cajaID FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ?";
$rs_caja_abierta = $db->obtenerTodo($sql_caja_abierta, array($id_usuario));

if (count($rs_caja_abierta) > 0) {
    $caja_id = $rs_caja_abierta[0]["cajaID"];
    $empresaID = $_SESSION['empresaID'];

    $db->beginTransaction();
    $ahora = date('Y-m-d H:i:s');
    try {
        // 1. Consolidar montos por forma de pago (Neto = Ingresos - Egresos)
        $sql_resumen = "SELECT formapagoID, SUM(monto_neto) as total 
                        FROM (
                            SELECT ip.formapagoID, SUM(ip.monto) as monto_neto 
                            FROM ingreso_pagos ip 
                            JOIN ingresos i ON ip.ingresoID = i.ingresoID 
                            WHERE i.cajaID = ? AND i._estado <> 'X' 
                            GROUP BY ip.formapagoID
                            UNION ALL
                            SELECT ep.formapagoID, SUM(ep.monto) * -1 as monto_neto 
                            FROM egreso_pagos ep 
                            JOIN egresos e ON ep.egresoID = e.egresoID 
                            WHERE e.cajaID = ? AND e._estado <> 'X' 
                            GROUP BY ep.formapagoID
                        ) as t GROUP BY formapagoID";

        $resumen = $db->obtenerTodo($sql_resumen, [$caja_id, $caja_id]);

        // 2. Insertar en cierre_cajas para el reporte administrativo
        foreach ($resumen as $row) {
            $sql_ins = "INSERT INTO cierre_cajas (cajaID, formapagoID, monto, _fec_insercion, _usuario, _estado) 
                        VALUES (?, ?, ?, ?, ?, 'A')";
            $db->ejecutar($sql_ins, [$caja_id, $row['formapagoID'], $row['total'], $ahora, $id_usuario]);
        }

        // 3. Actualizar la caja para cerrarla
        $sql_cerrar_caja = "UPDATE cajas SET estado = 'CERRADA', fecha_cierre = ?, _fec_modificacion = ?, _usuario = ? WHERE cajaID = ?";
        $db->ejecutar($sql_cerrar_caja, [$ahora, $ahora, $id_usuario, $caja_id]);

        $db->commit();
        $_SESSION['mensaje'] = 'Caja cerrada y consolidada exitosamente.';
        $_SESSION['mensaje_tipo'] = 'success';
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['mensaje'] = 'Error al cerrar caja: ' . $e->getMessage();
        $_SESSION['mensaje_tipo'] = 'danger';
    }
} else {
    // Si no se encuentra una caja abierta
    $_SESSION['mensaje'] = 'No hay ninguna caja abierta para cerrar.';
    $_SESSION['mensaje_tipo'] = 'danger';
}

// Redirigir de nuevo a la página principal de gestión de habitaciones
header("Location: habitaciones.php");
exit();
?>