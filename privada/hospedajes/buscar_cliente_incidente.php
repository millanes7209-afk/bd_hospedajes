<?php
session_start();
require_once("../../conexion.php");

$ci = isset($_POST['ci']) ? $_POST['ci'] : '';
$paisID = isset($_POST['paisID']) ? $_POST['paisID'] : '';

if (!empty($ci) && !empty($paisID)) {
    $sql = "SELECT c.*, p.nombre AS nombre_pais
            FROM clientes c
            INNER JOIN paises p ON c.paisID = p.paisID
            WHERE c.ci = ? 
            AND c.paisID = ? 
            AND c._estado <> 'X'";
    $fila = $db->obtenerFila($sql, [$ci, $paisID]);

    if ($fila) {
        $nombre_completo = trim($fila['nombres'] . ' ' . $fila['apellido1'] . ' ' . $fila['apellido2']);
        echo "<div class='alert alert-info d-flex justify-content-between align-items-center' style='padding: 10px 15px;'>";
        echo "  <p class='mb-0'><strong>Cliente:</strong> " . $fila['ci'] . " - " . $nombre_completo . "</p>";
        echo "  <button type='button' class='btn btn-primary btn-sm fw-bold' onclick='seleccionarCliente(" . $fila['clienteID'] . ",\"" . addslashes($nombre_completo) . "\",\"" . $fila['ci'] . "\")'>
                <i class='fas fa-plus-circle'></i> SELECCIONAR
            </button>";
        echo "</div>";
    } else {
        echo "<div class='alert alert-warning py-2 mb-0'>";
        echo "  <i class='fas fa-exclamation-triangle'></i> Cliente no encontrado en este país.";
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>Faltan datos de búsqueda.</div>";
}
?>