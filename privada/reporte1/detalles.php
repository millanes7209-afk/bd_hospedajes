<?php
require_once("../../conexion.php");

$fecha = $_GET['fecha'];  // Fecha pasada desde la consulta AJAX
$tipoReporte = $_GET['tipoReporte'];  // Recibir tipo de reporte (ingreso o egreso)

// Obtener el ID del usuario desde la sesión
$usuarioID = $_SESSION['sesion_id_usuario'];  

// Consultar los detalles para la fecha seleccionada, filtrando por tipo de movimiento y usuario
$queryDetalles = "SELECT
                    mc.descripcion,
                    mc.monto,
                    fp.tipo AS forma_pago
                  FROM movimientos_caja mc
                  LEFT JOIN formas_pago fp ON mc.formaPagoID = fp.formaPagoID
                  WHERE mc._estado = 'A' 
                    AND DATE(mc.fecha_hora) = '$fecha'";

// Si se seleccionó 'ingreso' o 'egreso', agregar el filtro para el tipo de movimiento
if ($tipoReporte === 'ingreso' || $tipoReporte === 'egreso') {
    $queryDetalles .= " AND mc.tipo_movimiento = '" . strtoupper($tipoReporte) . "'";
}

// Filtrar por usuario (para RECEPCIONISTA o si es ADMINISTRADOR/PROPIETARIO)
$rol = $_SESSION['sesion_rol'];
if ($rol === 'RECEPCIONISTA') {
    $queryDetalles .= " AND mc._usuario = " . intval($usuarioID);
}

// Ejecutar la consulta
$result = $db->Execute($queryDetalles);

$detalles = [];
if ($result) {
    while (!$result->EOF) {
        $detalles[] = [
            'descripcion' => $result->fields['descripcion'],
            'monto' => $result->fields['monto'],
            'forma_pago' => $result->fields['forma_pago']
        ];
        $result->MoveNext();
    }
}

// Enviar los detalles como respuesta JSON
echo json_encode($detalles);
?>
