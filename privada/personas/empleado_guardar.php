<?php
session_start();
require_once("../../conexion.php");

$empresaID = $_SESSION['empresaID'];

// Recibir datos del formulario
$ci = $_POST["ci"];
$nombres = $_POST["nombres"];
$apellidos = $_POST["apellidos"];
$telefono = $_POST["telefono"];
$genero = $_POST["genero"];
$fecha_nacimiento = $_POST["fecha_nacimiento"];

// Verificar si el CI ya existe
$sql_verificar = $db->Prepare("SELECT COUNT(*) as total FROM empleados WHERE ci = ? AND _estado <> 'X'");
$rs_verificar = $db->GetAll($sql_verificar, array($ci));

if ($rs_verificar[0]['total'] > 0) {
    // CI ya existe, redirigir con error
    header("Location: empleado_nuevo.php?ci=" . urlencode($ci) . "&error=ci_existe");
    exit();
}

// Insertar nuevo empleado
$reg = array();
$reg["ci"] = $ci;
$reg["nombres"] = $nombres;
$reg["apellidos"] = $apellidos;
$reg["telefono"] = $telefono;
$reg["genero"] = $genero;
$reg["fecha_nacimiento"] = $fecha_nacimiento;
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_estado"] = 'A';

$rs1 = $db->AutoExecute("empleados", $reg, "INSERT");

if ($rs1) {
    // Obtener el ID del empleado recién insertado
    $empleadoID = $db->Insert_ID();
    
    // Redirigir al formulario de contrato con el empleadoID
    header("Location: empleado_contrato.php?empleadoID=" . $empleadoID . "&nuevo=1");
} else {
    header("Location: empleado_nuevo.php?ci=" . urlencode($ci) . "&error=guardar");
}
exit();
?>
