<?php
session_start();
require_once("../../conexion.php");

// Obtener los datos del formulario
$ci = $_POST['ci'];
$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$lugar_nacimiento = $_POST['lugar_nacimiento'];
$est_civil = $_POST['estado_civil'];
$profesion = $_POST['profesion'];

try {
    // Comprobar si el C.I. ya está registrado
    $sql_verificar = "SELECT clienteID FROM clientes WHERE ci = ?";
    $stmt_verificar = $db->Prepare($sql_verificar);
    $rs_verificar = $db->Execute($stmt_verificar, array($ci));

    if ($rs_verificar && !$rs_verificar->EOF) {
        // Si el C.I. ya existe, devolver un mensaje de error específico
        echo "error_ci_duplicado";
    } else {
        // Proceder con la inserción del nuevo cliente si el C.I. no existe
        $reg = array();
        $reg["empresaID"]=1;
        $reg["ci"] = $ci;
        $reg["nombres"] = $nombres;
        $reg["apellidos"] = $apellidos;
        $reg["fecha_nacimiento"] = $fecha_nacimiento;
        $reg["lugar_nacimiento"] = $lugar_nacimiento;
        $reg["est_civil"] = $est_civil;
        $reg["profesion"] = $profesion;
        $reg["_fec_insercion"] = date("Y-m-d H:i:s");
        $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
        $reg["_estado"] = "A";

        // Intentar insertar el cliente usando AutoExecute
        $rs1 = $db->AutoExecute("clientes", $reg, "INSERT");

        if ($rs1) {
            $clienteID=$db->Insert_ID();
            echo "success:$clienteID";
        } else {
            echo "error: No se pudo registrar el cliente. Inténtelo de nuevo.";
        }
    }
} catch (Exception $e) {
    // Manejar cualquier otro error inesperado
    echo "error: " . $e->getMessage();
}
?>
