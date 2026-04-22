<?php
session_start();
require_once("../../conexion.php");
//$db->debug = true;
setlocale(LC_TIME, 'es_ES.UTF-8');

// Función para obtener el día de la semana en español
function obtenerDiaEnEspanol($fecha) {
    $dias = array("Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado");
    $numeroDia = date('w', strtotime($fecha));
    return $dias[$numeroDia];
}

if (!isset($_GET['tipoReporte']) || !isset($_GET['frecuencia'])) {
    echo "Por favor, seleccione un tipo de reporte y frecuencia.";
    exit;
}

$tipoReporte = $_GET['tipoReporte'];
$frecuencia = $_GET['frecuencia'];

$usuarioFiltradoID = $_GET['usuarioID'] ?? ''; 
$fechaInicio = $_GET['fechaInicio'] ?? '';
$fechaFin = $_GET['fechaFin'] ?? '';

// Captar el tipo de rol desde la sesión
$rol = $_SESSION['sesion_rol'];
$usuarioID = $_SESSION['sesion_id_usuario'];  // Usar la variable correcta para el ID del usuario

// Definir la consulta base
$query = "SELECT 
              mc.movimientocajaID, 
              mc.tipo_movimiento, 
              mc.descripcion, 
              mc.monto,
              fp.tipo AS forma_pago,
              DATE_FORMAT(mc.fecha_hora, '%Y-%m-%d %H:%i') AS fecha_hora 
          FROM movimientos_caja mc
          LEFT JOIN formas_pago fp ON mc.formaPagoID = fp.formaPagoID
          WHERE mc._estado = 'A'";

if ($rol === 'RECEPCIONISTA') {
    $query .= " AND _usuario = " . intval($usuarioID);
} elseif (($rol === 'PROPIETARIO' || $rol === 'ADMINISTRADOR') && !empty($usuarioFiltradoID)) {
    $query .= " AND _usuario = " . intval($usuarioFiltradoID);
}

// Filtrar por tipo de movimiento si es necesario
if ($tipoReporte === 'ingreso' || $tipoReporte === 'egreso') {
    $query .= " AND tipo_movimiento = '" . strtoupper($tipoReporte) . "'";
}

// Modificar el switch para filtrar según la frecuencia
switch ($frecuencia) {
    case 'intervalo':
        if (isset($_GET['fechaInicio']) && isset($_GET['fechaFin'])) {
            $fechaInicio = $_GET['fechaInicio'];
            $fechaFin = $_GET['fechaFin'];

            if (!empty($fechaInicio) && !empty($fechaFin)) {
                // Consulta para desglose por fecha y forma de pago
                $query = "SELECT 
                             DATE(fecha_hora) AS fecha, 
                             fp.tipo AS forma_pago,
                             SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE 0 END) AS total_ingresos,
                             SUM(CASE WHEN tipo_movimiento = 'EGRESO' THEN monto ELSE 0 END) AS total_egresos,
                             SUM(CASE WHEN tipo_movimiento = 'INGRESO' THEN monto ELSE -monto END) AS saldo
                          FROM movimientos_caja mc
                          LEFT JOIN formas_pago fp ON mc.formaPagoID = fp.formaPagoID
                          WHERE mc._estado = 'A'
                            AND DATE(mc.fecha_hora) BETWEEN '$fechaInicio' AND '$fechaFin'";

                if ($rol === 'RECEPCIONISTA') {
                    $query .= " AND mc._usuario = " . intval($usuarioID);
                } elseif (($rol === 'PROPIETARIO' || $rol === 'ADMINISTRADOR') && !empty($usuarioFiltradoID)) {
                    $query .= " AND mc._usuario = " . intval($usuarioFiltradoID);
                }

                if ($tipoReporte === 'ingreso' || $tipoReporte === 'egreso') {
                    $query .= " AND tipo_movimiento = '" . strtoupper($tipoReporte) . "'";
                }

                $query .= " GROUP BY DATE(fecha_hora), fp.tipo ORDER BY fecha_hora";
            } else {
                echo "Debe proporcionar fechas válidas.";
                exit;
            }
        } else {
            echo "Debe proporcionar fechas de inicio y fin.";
            exit;
        }
        break;
    case 'mensual':
    case 'anual':
        echo "Esta frecuencia no soporta el desglose por forma de pago.";
        exit;
}

// Obtener el nombre de usuario si se filtró por ID
$usuarioFiltrado = '';
if (!empty($usuarioFiltradoID)) {
    $queryUsuario = "SELECT u.usuario 
                     FROM usuarios u
                     WHERE u.id_usuario = " . intval($usuarioFiltradoID);
    
    $resultUsuario = $db->Execute($queryUsuario);

    if ($resultUsuario && !$resultUsuario->EOF) {
        $usuarioFiltrado = $resultUsuario->fields['usuario'];
    }
}

$result = $db->Execute($query);

if (!$result) {
    echo "Error en la consulta: " . $db->ErrorMsg();
    exit;
}

// Calcular totales
$totalIngresos = [];
$totalEgresos = [];
$totalSaldo = [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos y Egresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <!-- Mostrar el título dinámico del reporte -->
        <h2 class="text-center">
            <?php if (!empty($usuarioFiltrado)): ?>
                REPORTE DEL USUARIO: <?php echo htmlspecialchars($usuarioFiltrado); ?> <br>
            <?php endif; ?>
            DEL <?php echo date("d/m/Y", strtotime($fechaInicio)); ?> AL <?php echo date("d/m/Y", strtotime($fechaFin)); ?>
        </h2>

        <?php if ($result->RecordCount() > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped shadow-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Fecha (Día)</th>
                            <th>Forma de Pago</th>
                            <th>Ingresos (Bs)</th>
                            <th>Egresos (Bs)</th>
                            <th>Saldo (Bs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while (!$result->EOF): ?>
                            <tr>
                                <!-- Mostrar la fecha y el día de la semana -->
                                <td><?php echo date("d/m/Y", strtotime($result->fields['fecha'])); ?> (<?php echo obtenerDiaEnEspanol($result->fields['fecha']); ?>)</td>
                                <td><?php echo htmlspecialchars($result->fields['forma_pago']); ?></td>
                                <td><?php echo number_format($result->fields['total_ingresos'], 2); ?></td>
                                <td><?php echo number_format($result->fields['total_egresos'], 2); ?></td>
                                <td><?php echo number_format($result->fields['saldo'], 2); ?></td>
                            </tr>
                            <?php
                            // Acumular totales
                            $forma_pago = $result->fields['forma_pago'];
                            $totalIngresos[$forma_pago] = ($totalIngresos[$forma_pago] ?? 0) + $result->fields['total_ingresos'];
                            $totalEgresos[$forma_pago] = ($totalEgresos[$forma_pago] ?? 0) + $result->fields['total_egresos'];
                            $totalSaldo[$forma_pago] = ($totalSaldo[$forma_pago] ?? 0) + $result->fields['saldo'];
                            $result->MoveNext();
                            ?>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <?php foreach ($totalIngresos as $forma => $ingresos): ?>
                            <tr>
                                <td colspan="2"><strong>Total (<?php echo $forma; ?>):</strong></td>
                                <td><strong><?php echo number_format($ingresos, 2); ?> Bs.</strong></td>
                                <td><strong><?php echo number_format($totalEgresos[$forma], 2); ?> Bs.</strong></td>
                                <td><strong><?php echo number_format($totalSaldo[$forma], 2); ?> Bs.</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">No se encontraron registros para los usuarios seleccionados.</div>
        <?php endif; ?>
    </div>
</body>
</html>
