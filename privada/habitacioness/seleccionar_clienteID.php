<?php
session_start();
require_once("../../conexion.php");

// Comprobamos si se ha proporcionado el clienteID
if (!isset($_POST['clienteID']) || empty($_POST['clienteID'])) {
    echo "No se ha proporcionado un clienteID válido.";
    exit;
}

$clienteID = $_POST['clienteID'];

// Consulta para buscar el cliente por su ID en la base de datos
$sql = "SELECT clienteID, ci, nombres, apellidos FROM clientes WHERE clienteID = ? AND _estado = 'A'";
$rs = $db->GetAll($sql, array($clienteID));

if ($rs && count($rs) > 0) {
    // Mostrar la información del cliente encontrado
    foreach ($rs as $cliente) {
        echo "<div class='alert alert-info'>";
        echo "<strong>C.I.:</strong> " . htmlspecialchars($cliente['ci']) . " - <strong>Nombre:</strong> " . htmlspecialchars($cliente['nombres']) . " " . htmlspecialchars($cliente['apellidos']);
        echo "</div>";
    }
} else {
    echo "<div class='alert alert-warning'>No se encontró ningún cliente con el ID proporcionado.</div>";
}
?>
