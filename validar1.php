<?php
/**
 * Validación Final - Con empresa seleccionada
 */

session_start();
require_once("conexion.php");
require_once("funciones_caja.php");

// Verificar si hay sesión activa
if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: index.php");
    exit();
}

// Procesar selección de empresa
if (isset($_POST['empresaID'])) {
    $empresaID = $_POST['empresaID'];
    
    // Guardar empresaID en sesión
    $_SESSION['empresaID'] = $empresaID;
    
    // Obtener datos de la empresa para mostrar
    $sql_empresa = "SELECT * FROM empresa WHERE empresaID = ? AND _estado <> 'X'";
    $stmt = $db->prepare($sql_empresa);
    $stmt->execute([$empresaID]);
    $empresa = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($empresa) {
        $_SESSION['nombre_empresa'] = $empresa['nombre'];
        $_SESSION['datos_empresa'] = $empresa;
    }
    
    // Verificar caja abierta con empresaID
    $caja_abierta_id = verificarCajaAbierta($db, $_SESSION["sesion_id_usuario"], $empresaID);
    if ($caja_abierta_id) {
        $_SESSION['caja_abierta_id'] = $caja_abierta_id;
        
    } else {
        $_SESSION['caja_abierta_id'] = null;
    }
    // Dentro de validar1.php, antes de las redirecciones:
    unset($_SESSION['mensaje']); // Limpia mensajes previos

    if ($caja_abierta_id) {
        $_SESSION['caja_abierta_id'] = $caja_abierta_id;
        
    }
    // Redirigir a habitaciones.php
    header("Location: privada/habitacioness/habitaciones.php");
    exit();
    
} else {
    // Si no hay empresaID, volver al selector
    $_SESSION['mensaje'] = array('tipo' => 'warning', 'texto' => 'Debes seleccionar una empresa.');
    header("Location: selector_empresa.php");
    exit();
}
?>
