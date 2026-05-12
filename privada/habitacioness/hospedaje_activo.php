<?php
session_start();
require_once("../../conexion.php");

if (isset($_POST['clienteID'])) {
    $clienteID = $_POST['clienteID'];
    $empresaID = $_SESSION['empresaID']; // FILTRO CRÍTICO

    try {
        // Solo bloqueamos si el cliente está hospedado EN ESTA EMPRESA
        $sql = "SELECT hc.hospedajeID 
                FROM hospedajes_clientes hc
                JOIN hospedajes h ON hc.hospedajeID = h.hospedajeID
                WHERE hc.clienteID = ? 
                AND h.empresaID = ? 
                AND h.estado IN ('ACTIVO', 'DEUDA') 
                AND h._estado <> 'X'";
        
        $rs = $db->obtenerFila($sql, [$clienteID, $empresaID]);

        if ($rs) {
            echo 'hospedaje_activo';
        } else {
            echo 'no_hospedaje_activo';
        }
    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: Faltan datos necesarios.';
}
?>
