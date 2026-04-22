<?php
session_start();
require_once("../../conexion.php");

// Activar el modo de depuración para ver errores SQL
$db->debug = true;

// Obtener los datos del formulario
$id_grupo = $_POST["id_grupo"];
$opcion = $_POST["opcion"];
$contenido = $_POST["contenido"];
$orden = $_POST["orden"];

// Preparar los datos para la inserción
$reg = array();
$reg["id_grupo"] = $id_grupo;
$reg["opcion"] = $opcion;
$reg["contenido"] = $contenido;
$reg["orden"] = $orden;

$reg["_fec_insercion"] = date("Y-m-d H:i:s");
$reg["_estado"] = 'A';
$reg["_usuario"] = $_SESSION["sesion_id_usuario"];

// Insertar los datos en la base de datos
$rs1 = $db->AutoExecute("opciones", $reg, "INSERT"); 

// Verificar si la inserción fue exitosa
if ($rs1 === false) {
    echo "Error en la inserción: " . $db->ErrorMsg();
} else {
    header("Location: opciones.php");
    exit();
}
?>
