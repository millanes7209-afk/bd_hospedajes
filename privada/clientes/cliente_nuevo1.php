<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$ci = $_POST['ci'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$lugar_nacimiento = $_POST['lugar_nacimiento'];
$est_civil = $_POST['est_civil'];
$profesion = $_POST['profesion'];

// Verificar si el 'ci' ya existe
$sql = "SELECT * FROM clientes WHERE ci = ? AND _estado <> 'X'";
$existe = $db->GetOne($sql, array($ci));

if ($existe) {
    // Redirigir al formulario con un mensaje de error
    $_SESSION['mensaje'] = "El documento de identidad '$ci' ya está registrado.";
    $_SESSION['mensaje_tipo'] = "danger";
    header("Location: cliente_nuevo.php");
    exit();
} else {
    // Registrar el nuevo cliente
    $reg = array();
    $reg["ci"] = $ci;
    $reg["nombres"] = $nombres;
    $reg["apellidos"] = $apellidos;
    $reg["fecha_nacimiento"] = $fecha_nacimiento;
    $reg["lugar_nacimiento"] = $lugar_nacimiento;
    $reg["est_civil"] = $est_civil;
    $reg["profesion"] = $profesion;
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $reg["_estado"] = "A";

    $db->AutoExecute("clientes", $reg, "INSERT");

    // Redirigir con mensaje de éxito
    $_SESSION['mensaje'] = "Cliente registrado correctamente.";
    $_SESSION['mensaje_tipo'] = "success";
    header("Location: clientes.php");
    exit();
}
?>
