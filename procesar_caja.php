<?php
// Activar depuración si es necesario
//$db->debug = true;

session_start();
require_once("conexion.php");
require_once("funciones_caja.php"); // Validación de la caja abierta
// Obtener el usuario y empresa que realiza la acción
$usuarioID = $_SESSION["sesion_id_usuario"];
$empresaID = $_SESSION['empresaID'];

// Comprobar si se ha enviado una acción (abrir o cerrar)
if (isset($_POST['accion'])) {
    $accion = $_POST['accion'];

    if ($accion === 'abrir') {
        // Verificar si ya hay una caja abierta para este usuario
        $caja_abierta_id = verificarCajaAbierta($db, $usuarioID,$empresaID);
    
        if ($caja_abierta_id) {
            // Mensaje si ya existe una caja abierta
            $_SESSION['mensaje'] = "Ya tienes una caja abierta. Cierra esa caja antes de abrir otra.";
            header("Location: privada/habitacioness/habitaciones.php");
            exit();
        }
    
        // Si no hay caja abierta, abrir una nueva
        $fecha_apertura = date('Y-m-d H:i:s');
        
        $sql_abrir_caja = "INSERT INTO cajas (_fec_insercion, _usuario, usuarioID, empresaID, _estado, fecha_apertura, estado) 
            VALUES (NOW(), ?, ?, ?, 'A', ?, 'ABIERTA')
        ";
        
        $result = $db->ejecutar($sql_abrir_caja, array($usuarioID, $usuarioID, $empresaID, $fecha_apertura));
    
        if ($result) {
            // Guardar el ID de la nueva caja en la sesión
            $_SESSION['caja_abierta_id'] = $db->lastInsertId();
            $_SESSION['mensaje'] = "Caja abierta exitosamente.";
        } else {
            $_SESSION['mensaje'] = "Error al abrir la caja. Por favor, inténtalo de nuevo.";
        }
    }
    elseif ($accion === 'cerrar') {
        // Verificar si hay una caja abierta
        $caja_abierta_id = verificarCajaAbierta($db, $usuarioID,$empresaID);
    
        if (!$caja_abierta_id) {
            // Mensaje si no hay caja abierta para cerrar
            $_SESSION['mensaje'] = "No tienes ninguna caja abierta para cerrar.";
            header("Location: privada/habitacioness/habitaciones.php");
            exit();
        }
    
        // Cerrar la caja abierta
        $fecha_cierre = date('Y-m-d H:i:s');
        
        $sql_cerrar_caja = "UPDATE cajas 
            SET fecha_cierre = ?, estado = 'CERRADA' 
            WHERE cajaID = ? AND usuarioID = ? AND empresaID = ?";
        $result = $db->ejecutar($sql_cerrar_caja, array($fecha_cierre, $caja_abierta_id, $usuarioID, $empresaID));
    
        if ($result) {
            // Obtener saldos por forma de pago antes de cerrar
            $sql_saldos = "SELECT fp.formapagoID, fp.tipo, SUM(m.monto) as total_monto
                           FROM movimientos m
                           INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
                           WHERE m.cajaID = ? AND m.empresaID = ?
                           GROUP BY fp.formapagoID
                           HAVING SUM(m.monto) > 0";
            $saldos = $db->obtenerTodo($sql_saldos, array($caja_abierta_id, $empresaID));
            
            // Crear snapshots en cierre_cajas
            foreach ($saldos as $saldo) {
                $sql_snapshot = "INSERT INTO cierre_cajas (cajaID, formapagoID, monto) VALUES (?, ?, ?)";
                $db->ejecutar($sql_snapshot, array($caja_abierta_id, $saldo['formapagoID'], $saldo['total_monto']));
            }
            
            // Limpiar la caja abierta en la sesión
            $_SESSION['caja_abierta_id'] = null;
            $_SESSION['mensaje'] = "Caja cerrada exitosamente. Snapshots guardados.";
        } else {
            $_SESSION['mensaje'] = "Error al cerrar la caja. Por favor, inténtalo de nuevo.";
        }
    }
    else {
        $_SESSION['mensaje'] = "Acción no válida.";
    }
} else {
    $_SESSION['mensaje'] = "No se recibió ninguna acción.";
}

// Redirigir al mapa de habitaciones
header("Location: privada/habitacioness/habitaciones.php");
exit();
?>
