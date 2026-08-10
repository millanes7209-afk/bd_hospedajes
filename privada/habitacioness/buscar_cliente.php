<?php
session_start();
require_once("../../conexion.php");

if (!isset($_POST['ci']) || empty($_POST['ci'])) {
    echo "No se ha proporcionado un número de C.I. válido.";
    exit;
}

$ci = $_POST['ci'];

// Consulta para buscar coincidencias en la base de datos
$sql = "SELECT clienteID, ci, nombres, apellidos FROM clientes WHERE ci LIKE ? AND _estado = 'A'";
$rs = $db->obtenerTodo($sql, array($ci . '%')); // Utilizamos LIKE para buscar coincidencias parciales al inicio

if ($rs && count($rs) > 0) {
    echo "<div class='alert alert-success'>Coincidencias encontradas:</div>";
    echo "<ul class='list-group'>";
    foreach ($rs as $cliente) {
        echo "<li class='list-group-item'>";
        echo "<input type='radio' name='clienteID' value='" . $cliente['clienteID'] . "' onclick='seleccionarCliente(" . $cliente['clienteID'] . ")'> ";
        echo "<strong>C.I.:</strong> " . htmlspecialchars($cliente['ci']) . " - <strong>Nombre:</strong> " . htmlspecialchars($cliente['nombres']) . " " . htmlspecialchars($cliente['apellidos']);
        echo "</li>";
    }
    echo "</ul>";
} else {
    // Si no se encuentra ninguna coincidencia
    echo "<div class='alert alert-warning'>No se encontraron coincidencias para el C.I. ingresado. Puede registrar un nuevo cliente.</div>";
}
?>
