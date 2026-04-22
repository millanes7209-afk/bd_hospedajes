<?php
session_start();
require_once("../../conexion.php");

header('Content-Type: text/plain'); // Para depuración

$empresaID = $_SESSION['empresaID'];

// Depuración: mostrar todos los datos recibidos
echo "=== DEPURACIÓN empleado_contrato_guardar.php ===\n";
echo "POST recibido:\n";
print_r($_POST);
echo "\n";

// Recibir datos del formulario
$empleadoID = $_POST["empleadoID"] ?? '';
$rol = $_POST["rol"] ?? '';
$sueldo = $_POST["sueldo"] ?? '';
$fecha_inicio = $_POST["fecha_inicio"] ?? '';
$fecha_fin = $_POST["fecha_fin"] ?? '';

// Campos opcionales (null si no existen o están vacíos)
$es_titular = (isset($_POST["es_titular"]) && $_POST["es_titular"] !== '') ? $_POST["es_titular"] : null;
// estado_laboral siempre se llena con "ACTIVO" para nuevos contratos
$estado_laboral = "ACTIVO";

echo "Datos procesados:\n";
echo "empleadoID: $empleadoID\n";
echo "empresaID: $empresaID\n";
echo "rol: $rol\n";
echo "sueldo: $sueldo\n";
echo "fecha_inicio: $fecha_inicio\n";
echo "fecha_fin: $fecha_fin\n";
echo "es_titular: " . ($es_titular === null ? 'NULL' : $es_titular) . "\n";
echo "estado_laboral: " . ($estado_laboral === null ? 'NULL' : $estado_laboral) . "\n";

// Validar datos requeridos
if (empty($empleadoID) || empty($rol) || empty($sueldo) || empty($fecha_inicio)) {
    echo "ERROR: Faltan datos requeridos\n";
    exit("ERROR: Faltan datos requeridos");
}

// Insertar nuevo contrato en empleado_empresas
$reg = array();
$reg["empleadoID"] = $empleadoID;
$reg["empresaID"] = $empresaID;
$reg["rol"] = $rol;
$reg["sueldo"] = $sueldo;
$reg["fecha_inicio"] = $fecha_inicio;
$reg["fecha_fin"] = $fecha_fin;
$reg["es_titular"] = $es_titular;
$reg["estado_laboral"] = $estado_laboral;
$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];
$reg["_estado"] = 'A';

echo "Datos a insertar:\n";
print_r($reg);

echo "=== DEPURACIÓN ANTES DE INSERTAR ===\n";
echo "SQL que se ejecutará: INSERT INTO empleado_empresas\n";
echo "Valores a insertar:\n";
foreach ($reg as $key => $value) {
    echo "  $key => " . ($value === null ? 'NULL' : "'$value'") . "\n";
}

echo "\n=== EJECUTANDO AUTOEXECUTE ===\n";
$rs1 = $db->AutoExecute("empleado_empresas", $reg, "INSERT");

echo "=== RESULTADO DE AUTOEXECUTE ===\n";
echo "rs1: " . ($rs1 ? 'TRUE' : 'FALSE') . "\n";
echo "Error message: " . $db->ErrorMsg() . "\n";
echo "Error code: " . $db->ErrorNo() . "\n";

if ($rs1) {
    echo "=== ÉXITO ===\n";
    echo "ID insertado: " . $db->Insert_ID() . "\n";
    echo "SUCCESS";
} else {
    echo "=== ERROR DETALLADO ===\n";
    echo "No se pudo guardar el contrato\n";
    echo "Error de BD: " . $db->ErrorMsg() . "\n";
    echo "Código de error: " . $db->ErrorNo() . "\n";
    
    // Intentar ejecutar la consulta manualmente para más detalles
    echo "\n=== INTENTANDO CONSULTA MANUAL ===\n";
    $sql_manual = "INSERT INTO empleado_empresas (empleadoID, empresaID, rol, sueldo, fecha_inicio, fecha_fin, es_titular, estado_laboral, _fec_insercion, _usuario, _estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    echo "SQL: $sql_manual\n";
    echo "Params: " . json_encode([$empleadoID, $empresaID, $rol, $sueldo, $fecha_inicio, $fecha_fin, $es_titular, $estado_laboral, $reg["_fec_insercion"], $reg["_usuario"], $reg["_estado"]]) . "\n";
    
    $stmt = $db->Prepare($sql_manual);
    $rs_manual = $db->Execute($stmt, [$empleadoID, $empresaID, $rol, $sueldo, $fecha_inicio, $fecha_fin, $es_titular, $estado_laboral, $reg["_fec_insercion"], $reg["_usuario"], $reg["_estado"]]);
    
    if ($rs_manual) {
        echo "CONSULTA MANUAL: ÉXITO\n";
        echo "ID insertado: " . $db->Insert_ID() . "\n";
    } else {
        echo "CONSULTA MANUAL: ERROR\n";
        echo "Error manual: " . $db->ErrorMsg() . "\n";
        echo "Error code manual: " . $db->ErrorNo() . "\n";
    }
    
    echo "ERROR";
}
?>
