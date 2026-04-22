<?php
session_start();
require_once("../../conexion.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se ha recibido al menos un cliente
if (!isset($_POST['clientesSeleccionados']) || count($_POST['clientesSeleccionados']) == 0) {
    echo "<p class='alert alert-danger'>Error: Debe agregar al menos un cliente antes de registrar el incidente.</p>";
    exit();
}

// Verificar si se ha recibido la descripción
if (empty($_POST["descripcion"])) {
    echo "<p class='alert alert-danger'>Error: La descripción del incidente es obligatoria.</p>";
    exit();
}

// Recoger los datos del formulario
$clientesSeleccionados = $_POST["clientesSeleccionados"]; // Array de clientes seleccionados
$descripcion = trim($_POST["descripcion"]); // Descripción del incidente

// Validar la longitud de la descripción
if (strlen($descripcion) > 255) {
    echo "<p class='alert alert-danger'>Error: La descripción no puede exceder los 255 caracteres.</p>";
    exit();
}

// Insertar un incidente por cada cliente seleccionado
foreach ($clientesSeleccionados as $clienteID) {
    // Preparar el registro para insertar en la tabla "incidentes"
    $reg = array();
    $reg["clienteID"] = $clienteID;
    $reg["descripcion"] = $descripcion;
    $reg["fecha"] = date("Y-m-d H:i:s");
    $reg["_fec_insercion"] = date("Y-m-d H:i:s");
    $reg["_estado"] = 'A';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];

    // Insertar el registro en la tabla "incidentes"
    $rs1 = $db->AutoExecute("incidentes", $reg, "INSERT");

    // Verificar si la inserción fue exitosa
    if (!$rs1) {
        echo "<p class='alert alert-danger'>Error al registrar el incidente para el cliente ID: $clienteID.</p>";
        exit();
    }
}
header("Location: incidentes.php");
// Mostrar mensaje de éxito
echo "<p class='alert alert-success'>El incidente se registró correctamente.</p>";
?>