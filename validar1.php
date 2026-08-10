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

// Procesar selección de empresa (Soporta POST normal o GET para Salto Maestro de Admin)
$empresaID = null;
if (isset($_POST['empresaID'])) {
    $empresaID = $_POST['empresaID'];
} elseif (isset($_GET['id']) && strtoupper($_SESSION['sesion_rol'] ?? '') === 'ADMINISTRADOR') {
    $empresaID = $_GET['id'];
}

if ($empresaID) {
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
    
    // Redirigir al selector de roles para esta empresa específica
    header("Location: selector_rol.php");
    exit();
} else {
    // Si no hay empresaID, volver al selector
    header("Location: selector_empresa.php");
    exit();
}
?>
