<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$ci = $_POST['ci'];
$nombres = strtoupper($_POST['nombres']);
$apellidos = strtoupper($_POST['apellidos']);
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$lugar_nacimiento = strtoupper($_POST['lugar_nacimiento']);
$est_civil = $_POST['estado_civil'];
$profesion = strtoupper($_POST['profesion']);
$empresaID = $_SESSION['empresaID'];
$usuarioID = $_SESSION["sesion_id_usuario"];

try {
    // 1. Comprobar si el C.I. ya está registrado
    $sql_verificar = "SELECT clienteID FROM clientes WHERE ci = ?";
    $cliente_existente = $db->obtenerFila($sql_verificar, [$ci]);

    if ($cliente_existente) {
        echo "error_ci_duplicado";
    } else {
        // 2. Insertar nuevo cliente
        $ahora = date('Y-m-d H:i:s');
        $sql_insert = "INSERT INTO clientes (empresaID, ci, nombres, apellidos, fecha_nacimiento, lugar_nacimiento, est_civil, profesion, _fec_insercion, _usuario, _estado) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'A')";

        $params = [
            $empresaID,
            $ci,
            $nombres,
            $apellidos,
            $fecha_nacimiento,
            $lugar_nacimiento,
            $est_civil,
            $profesion,
            $ahora,
            $usuarioID
        ];

        if ($db->ejecutar($sql_insert, $params)) {
            $clienteID = $db->lastInsertId();
            echo "success:$clienteID";
        } else {
            echo "error: No se pudo registrar el cliente. Inténtelo de nuevo.";
        }
    }
} catch (Exception $e) {
    echo "error: " . $e->getMessage();
}
?>