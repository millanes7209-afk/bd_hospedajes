<?php
session_start();
require_once("../../conexion.php");

// 1. Recibir datos por POST y limpiar espacios
$ci               = isset($_POST['ci']) ? trim($_POST['ci']) : '';
$nombres          = isset($_POST['nombres']) ? trim($_POST['nombres']) : '';
$apellido1        = isset($_POST['apellido1']) ? trim($_POST['apellido1']) : '';
$apellido2        = isset($_POST['apellido2']) ? trim($_POST['apellido2']) : '';
$fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
$lugar_nacimiento = isset($_POST['lugar_nacimiento']) ? trim($_POST['lugar_nacimiento']) : '';
$estado_civil     = isset($_POST['estado_civil']) ? trim($_POST['estado_civil']) : '';
$profesion        = isset($_POST['profesion']) ? trim($_POST['profesion']) : '';
$paisID           = isset($_POST['paisID']) ? (int)$_POST['paisID'] : null;

// 2. Extraer el usuario de la sesión de forma segura
$usuarioID = isset($_SESSION["sesion_id_usuario"]) ? (int)$_SESSION["sesion_id_usuario"] : null;

// Validación de sesión activa
if (!$usuarioID) {
    echo "error: No existe una sesión activa. Por favor, inicie sesión.";
    exit;
}

// 3. Validación de campos obligatorios
if (!empty($ci) && !empty($nombres) && !empty($apellido1) && !empty($fecha_nacimiento) && !empty($paisID)) {
    
    // 4. Verificar duplicados (Usando el método de tu nueva clase)
    $sql_check = "SELECT clienteID FROM clientes WHERE ci = ? AND paisID = ? AND _estado <> 'X'";
    $existe = $db->obtenerFila($sql_check, [$ci, $paisID]);
    
    if ($existe) {
        echo "error_ci_duplicado";
        exit;
    }

    // 5. Preparar el INSERT con parámetros (?) para seguridad total
    $sql_insert = "INSERT INTO clientes (
                    ci, nombres, apellido1, apellido2, fecha_nacimiento, 
                    lugar_nacimiento, estado_civil, profesion, paisID, 
                    _fec_insercion, _fec_modificacion, _usuario, _estado
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = [
        $ci, 
        $nombres, 
        $apellido1, 
        $apellido2, 
        $fecha_nacimiento, 
        $lugar_nacimiento, 
        $estado_civil, 
        $profesion, 
        $paisID,
        date("Y-m-d H:i:s"), 
        date("Y-m-d H:i:s"), 
        $usuarioID, 
        'A'
    ];

    // 6. Ejecutar usando el método universal de tu clase
    $res = $db->ejecutar($sql_insert, $params);
    
    // 7. Responder al AJAX
    if ($res) {
        // lastInsertId() es el estándar de PDO para obtener el ID autonumérico
        $nuevoID = $db->lastInsertId();
        echo "success:" . $nuevoID;
    } else {
        echo "error: Error técnico al registrar en la base de datos.";
    }

} else {
    echo "error: Faltan datos obligatorios para el registro.";
}
?>