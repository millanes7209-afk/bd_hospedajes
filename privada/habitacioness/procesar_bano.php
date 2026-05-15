<?php
session_start();
require_once("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empresaID = $_SESSION['empresaID'];
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $cajaID = $_SESSION['caja_abierta_id'];
    
    $monto = (float)$_POST['monto'];
    $tipo = $_POST['tipo']; // INGRESO o EGRESO
    $descripcion = strtoupper(trim($_POST['descripcion'] ?? ''));

    if (!$cajaID) {
        $_SESSION['mensaje'] = "Error: Debe tener una caja abierta.";
        $_SESSION['mensaje_tipo'] = "danger";
        header("Location: habitaciones.php");
        exit();
    }

    try {
        $sql = "INSERT INTO banos (empresaID, cajaID, usuarioID, monto, tipo, descripcion, fecha) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $db->ejecutar($sql, [$empresaID, $cajaID, $usuarioID, $monto, $tipo, $descripcion]);

        $_SESSION['mensaje'] = "Registro de Baño ($tipo) guardado correctamente.";
        $_SESSION['mensaje_tipo'] = "success";
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al guardar: " . $e->getMessage();
        $_SESSION['mensaje_tipo'] = "danger";
    }

    header("Location: habitaciones.php");
    exit();
}
?>
