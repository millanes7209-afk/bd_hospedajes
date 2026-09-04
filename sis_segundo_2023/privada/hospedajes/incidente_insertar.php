<?php
session_start();
require_once("../../conexion.php");

if ($_POST) {
    $clienteID = $_POST['clienteID'];
    $empresaID = $_SESSION['empresaID'];
    $descripcion = $_POST['descripcion'];
    $fecha = $_POST['fecha_hora'];
    $estado = $_POST['estado'];
    $usuarioID = $_SESSION['sesion_id_usuario'];

    $sql = "INSERT INTO incidentes (
                clienteID, empresaID, descripcion, fecha, estado, 
                usuarioID, _usuario, _fec_insercion, _estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'A')";

    $params = [
        $clienteID,
        $empresaID,
        $descripcion,
        $fecha,
        $estado,
        $usuarioID,
        $usuarioID
    ];

    if ($db->ejecutar($sql, $params)) {
        header("Location: incidentes.php?status=success");
    } else {
        echo "Error al registrar el incidente.";
    }
}
?>