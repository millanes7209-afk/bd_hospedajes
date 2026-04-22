<?php
require_once("../../conexion.php");

if (isset($_POST['clienteID'])) {
    $clienteID = $_POST['clienteID'];

    try {
        // Consulta para verificar si el cliente tiene un hospedaje activo
        $sql = $db->Prepare("SELECT hc.hospedajeID 
                             FROM hospedajes_clientes hc
                             JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID
                             WHERE hc.clienteID = ? AND h.estado = 'ACTIVO' AND h._estado <> 'X'");
        $rs = $db->GetRow($sql, array($clienteID));

        if ($rs) {
            // Si existe un hospedaje activo
            echo 'hospedaje_activo';
        } else {
            // Si no hay hospedaje activo
            echo 'no_hospedaje_activo';
        }
    } catch (Exception $e) {
        // Si hay un error en la consulta, devolver el error
        echo 'error: ' . $e->getMessage();
    }
} else {
    // Si no se proporciona un ID de cliente, devolver un error
    echo 'error: Faltan datos necesarios.';
}
