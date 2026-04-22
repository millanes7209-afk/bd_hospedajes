<?php
header('Content-Type: application/json');

session_start();
require_once("../../conexion.php");

$reservaID = $_POST["reservaID"];

// Verificar si la reserva tiene registros relacionados
$tablas = [];
$sqlHospedajes = $db->Prepare("SELECT * FROM hospedajes WHERE reservaID = ? AND _estado <> 'X'");
$rsHospedajes = $db->GetAll($sqlHospedajes, array($reservaID));
if ($rsHospedajes) {
    $tablas[] = 'hospedajes';
}

$sqlPagos = $db->Prepare("SELECT * FROM pagos WHERE reservaID = ? AND _estado <> 'X'");
$rsPagos = $db->GetAll($sqlPagos, array($reservaID));
if ($rsPagos) {
    $tablas[] = 'pagos';
}

if (!empty($tablas)) {
    // Formatear las tablas en formato de lista
    $tablaHerencia = '<ul>';
    foreach ($tablas as $tabla) {
        $tablaHerencia .= "<li>$tabla</li>";
    }
    $tablaHerencia .= '</ul>';

    // Enviar mensaje de error en JSON
    echo json_encode([
        'tipo' => 'danger',
        'mensaje' => "No se pudo eliminar la reserva porque tiene registros en las siguientes tablas:" . $tablaHerencia
    ]);
} else {
    // Marcar la reserva como eliminada ('X')
    $reg = array();
    $reg["_estado"] = 'X';
    $reg["_usuario"] = $_SESSION["sesion_id_usuario"];
    $rs1 = $db->AutoExecute("reservas", $reg, "UPDATE", "reservaID = '".$reservaID."'");

    echo json_encode([
        'tipo' => 'success',
        'mensaje' => "La reserva ha sido eliminada correctamente."
    ]);
}
?>
