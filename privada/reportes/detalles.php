<?php
session_start();
require_once("../../conexion.php");

// Obtener fecha y tipo de reporte
$fecha = $_GET['fecha'];  
$tipoReporte = $_GET['tipoReporte'];  

// Obtener el ID del usuario desde la sesión
$usuarioID = $_SESSION['sesion_id_usuario'];  
$rol = $_SESSION['sesion_rol'];

// Crear la consulta base
$queryDetalles = "SELECT
                    mc.descripcion,
                    mc.monto,
                    mc.fecha_hora,
                    fp.tipo AS forma_pago,
                    mc.tipo_movimiento as tipo
                  FROM movimientos_caja mc
                  LEFT JOIN formas_pago fp ON mc.formaPagoID = fp.formaPagoID
                  WHERE mc._estado = 'A' 
                    AND DATE(mc.fecha_hora) = '$fecha'";

// Filtrar por tipo de movimiento si se proporciona
if ($tipoReporte === 'ingreso' || $tipoReporte === 'egreso') {
    $queryDetalles .= " AND mc.tipo_movimiento = '" . strtoupper($tipoReporte) . "'";
}

// Filtrar por usuario según el rol
if ($rol === 'RECEPCIONISTA') {
    // Si es RECEPCIONISTA, mostrar solo sus registros
    $queryDetalles .= " AND mc._usuario = " . intval($usuarioID);
} elseif ($rol === 'ADMINISTRADOR' || $rol === 'PROPIETARIO') {
    // Si es ADMINISTRADOR o PROPIETARIO, puede ver todos los registros (o puede pasar un usuarioID específico)
    if (isset($_GET['usuarioID']) && $_GET['usuarioID'] !== '') {
        $usuarioFiltradoID = $_GET['usuarioID'];
        $queryDetalles .= " AND mc._usuario = " . intval($usuarioFiltradoID);
    }
}

// Ejecutar la consulta
$result = $db->Execute($queryDetalles);

$detalles = [];
if ($result) {
    while (!$result->EOF) {
        $detalles[] = [
            'descripcion' => $result->fields['descripcion'],
            'monto' => $result->fields['monto'],
            'fecha_hora' => $result->fields['fecha_hora'],
            'forma_pago' => $result->fields['forma_pago'],
            'tipo' => $result->fields['tipo']
        ];
        $result->MoveNext();
    }
}

// Enviar los detalles como respuesta JSON
echo json_encode($detalles);
?>
