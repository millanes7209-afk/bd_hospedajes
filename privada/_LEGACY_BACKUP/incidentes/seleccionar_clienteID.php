<?php
session_start();
require_once("../../conexion.php");

// Comprobamos si se ha proporcionado el clienteID
if (!isset($_POST['clienteID']) || empty($_POST['clienteID'])) {
    echo "No se ha proporcionado un clienteID válido.";
    exit;
}

$clienteID = $_POST['clienteID'];

// Consulta para buscar el cliente por su ID y obtener el número de visitas e incidentes
$sql = "
    SELECT 
        c.clienteID, 
        c.ci, 
        c.nombres, 
        c.apellidos,
        COUNT(v.visitaID) AS num_visitas,
        GROUP_CONCAT(i.descripcion ORDER BY i.fecha DESC) AS incidentes
    FROM clientes c
    LEFT JOIN visitas v ON c.clienteID = v.clienteID AND v._estado = 'A'
    LEFT JOIN incidentes i ON c.clienteID = i.clienteID AND i._estado = 'A'
    WHERE c.clienteID = ? AND c._estado = 'A'
    GROUP BY c.clienteID
";

$rs = $db->GetRow($sql, array($clienteID));

if ($rs) {
    // Mostrar la información del cliente encontrado
    echo "<div class='alert alert-info'>";
    echo "<strong>C.I.:</strong> " . htmlspecialchars($rs['ci']) . " - <strong>Nombre:</strong> " . htmlspecialchars($rs['nombres']) . " " . htmlspecialchars($rs['apellidos']);
    echo "<br><strong>Visitas:</strong> " . $rs['num_visitas'];
    echo "<br><strong>Incidentes:</strong> " . ($rs['incidentes'] ? htmlspecialchars($rs['incidentes']) : "No tiene incidentes registrados");
    echo "</div>";
} else {
    echo "<div class='alert alert-warning'>No se encontró ningún cliente con el ID proporcionado.</div>";
}
?>
