<?php
session_start();
require_once("../../conexion.php");

/**
 * PROCESADOR DE REVISIÓN DE AUDITORÍA
 * Marca un registro como fiscalizado por el administrador.
 */

$auditoriaID = $_POST['auditoriaID'] ?? null;
$empresaID = $_SESSION["empresaID"] ?? null;
$rolActual = $_SESSION["sesion_rol"] ?? '';

// SEGURIDAD: Solo Administradores o Propietarios pueden procesar auditorías
if (!in_array($rolActual, ['ADMINISTRADOR', 'PROPIETARIO'])) {
    die("Acceso denegado.");
}

if (!$auditoriaID || !$empresaID) {
    die("Datos insuficientes.");
}

$sql = "UPDATE auditorias 
        SET estado_revision = 1 
        WHERE auditoriaID = ? AND empresaID = ?";

if ($db->ejecutar($sql, [$auditoriaID, $empresaID])) {
    $_SESSION['mensaje'] = "Registro marcado como revisado/fiscalizado.";
} else {
    $_SESSION['mensaje'] = "Error al actualizar el estado de revisión.";
}

header("Location: hospedajes_auditoria.php");
exit();
?>