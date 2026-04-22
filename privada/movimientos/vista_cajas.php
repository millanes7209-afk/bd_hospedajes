<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['sesion_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener parámetros de filtro
$empresaID_filtro = $_SESSION['empresaID'];
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener usuarioID actual: REGLA DE NEGOCIO ESTRICTA "Cada usuario solo ve su propio historial"
$usuarioID_actual = $_SESSION['sesion_id_usuario'] ?? $_SESSION['usuarioID'] ?? 0;

// Obtener formas de pago disponibles
$sql_formas_pago = "SELECT fp.formapagoID, fp.tipo 
                   FROM formas_pago fp 
                   WHERE fp.empresaID = ? AND fp._estado <> 'X'
                   ORDER BY fp.tipo";
$formas_pago = $db->obtenerTodo($sql_formas_pago, [$empresaID_filtro]);

// Generar vista semanal basada en movimientos
$vista_semanal = [];
$fechas_rango = [];

// Array para sumatorias finales (footer)
$suma_footer_formas = [];
$suma_footer_total_general = 0;
foreach ($formas_pago as $fp) {
    $suma_footer_formas[$fp['tipo']] = 0;
}

// Generar rango de fechas
$fecha_actual = new DateTime($fecha_inicio);
$fecha_fin_obj = new DateTime($fecha_fin);
while ($fecha_actual <= $fecha_fin_obj) {
    $fecha_str = $fecha_actual->format('Y-m-d');
    $fechas_rango[] = $fecha_str;
    $vista_semanal[$fecha_str] = [
        'fecha' => $fecha_str,
        'movimientos' => []
    ];
    $fecha_actual->add(new DateInterval('P1D'));
}

// Para cada fecha, obtener movimientos
foreach ($fechas_rango as $fecha) {
    // Obtener movimientos del día SOLO DEL USUARIO y SOLO CAJAS CERRADAS
    $sql_movimientos_dia = "SELECT 
                              m.movimientoID,
                              m.usuarioID,
                              m.cajaID,
                              m.tipo,
                              m.concepto,
                              m.monto,
                              m._fec_insercion,
                              u.usuario as nombre_usuario,
                              fp.tipo as forma_pago
                            FROM movimientos m
                            INNER JOIN usuarios u ON m.usuarioID = u.usuarioID
                            INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
                            INNER JOIN cajas c ON m.cajaID = c.cajaID
                            WHERE DATE(m._fec_insercion) = ? 
                              AND m.empresaID = ? 
                              AND m._estado = 'A' 
                              AND c.estado = 'CERRADA'
                              AND m.usuarioID = ?
                            ORDER BY m._fec_insercion DESC";
    
    $params_mov = [$fecha, $empresaID_filtro, $usuarioID_actual];
    
    $movimientos_dia = $db->obtenerTodo($sql_movimientos_dia, $params_mov);
    
    // Agrupar movimientos por usuario y caja
    $agrupados = [];
    foreach ($movimientos_dia as $mov) {
        $clave = $mov['cajaID'];
        
        if (!isset($agrupados[$clave])) {
            $agrupados[$clave] = [
                'usuario' => $mov['nombre_usuario'],
                'saldos' => [],
                'movimientos_count' => 0
            ];
        }
        
        // Acumular saldos por forma de pago
        $forma_pago = $mov['forma_pago'];
        if (!isset($agrupados[$clave]['saldos'][$forma_pago])) {
            $agrupados[$clave]['saldos'][$forma_pago] = 0;
        }
        
        // Sumar o restar según tipo de movimiento
        if ($mov['tipo'] == 'INGRESO') {
            $agrupados[$clave]['saldos'][$forma_pago] += $mov['monto'];
        } else {
            $agrupados[$clave]['saldos'][$forma_pago] -= $mov['monto'];
        }
        
        $agrupados[$clave]['movimientos_count']++;
    }
    
    $vista_semanal[$fecha]['movimientos'] = array_values($agrupados);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Cajas Cerradas</title>
</head>
<body>
    <div class="container-fluid mt-4 mb-5">
        <h2>Reporte de Mis Turnos Finalizados</h2>
        
        <!-- Filtros -->
        <div class="row mt-3 mb-4">
            <div class="col-md-3">
                <label for="fecha_inicio" class="fw-bold">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-3">
                <label for="fecha_fin" class="fw-bold">Fecha Fin:</label>
                <input type="date" id="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button onclick="filtrar()" class="btn btn-primary w-100 fw-bold"><i class="fas fa-filter"></i> Filtrar Fechas</button>
            </div>
        </div>
        
        <!-- Tabla -->
        <div class="table-responsive shadow-sm rounded">
            <table class="table table-bordered table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Usuario Autor</th>
                        <th class="text-center">Operaciones</th>
                        <?php foreach ($formas_pago as $forma_pago): ?>
                            <th class="text-center bg-secondary"><?= $forma_pago['tipo'] ?></th>
                        <?php endforeach; ?>
                        <th class="text-end bg-success text-white">Ingreso Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $hayRegistros = false;
                    foreach ($vista_semanal as $fecha => $datos): ?>
                        <?php if (empty($datos['movimientos'])): ?>
                            <tr>
                                <td colspan="<?= 3 + count($formas_pago) + 1 ?>" class="text-center text-muted fst-italic">
                                    <?= date('d/m/Y', strtotime($fecha)) ?> - Sin cajas cerradas
                                </td>
                            </tr>
                        <?php else: 
                            $hayRegistros = true;
                            foreach ($datos['movimientos'] as $movimiento): ?>
                                <tr>
                                    <td class="fw-bold text-center align-middle"><?= date('d/m/Y', strtotime($fecha)) ?></td>
                                    <td class="text-center align-middle"><i class="fas fa-user-circle text-primary"></i> <?= $movimiento['usuario'] ?></td>
                                    <td class="text-center align-middle"><span class="badge bg-secondary"><?= $movimiento['movimientos_count'] ?> req.</span></td>
                                    
                                    <?php 
                                    $total_fila = 0;
                                    foreach ($formas_pago as $forma_pago): 
                                        $monto = $movimiento['saldos'][$forma_pago['tipo']] ?? 0;
                                        $total_fila += $monto;
                                        $suma_footer_formas[$forma_pago['tipo']] += $monto; // Suman para el pie de página
                                    ?>
                                        <td class="text-center align-middle">Bs. <?= number_format($monto, 2) ?></td>
                                    <?php endforeach; ?>
                                    
                                    <?php $suma_footer_total_general += $total_fila; ?>
                                    <td class="text-end align-middle fw-bold" style="color: #198754;">Bs. <?= number_format($total_fila, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
                
                <?php if($hayRegistros): ?>
                <tfoot style="border-top: 3px solid #333; background-color: #f8f9fa;">
                    <tr>
                        <td colspan="3" class="text-end text-uppercase fw-bold fs-6 pt-3 pb-3">Suma Total del Rango:</td>
                        <?php foreach ($formas_pago as $forma_pago): ?>
                            <td class="text-center fw-bold text-dark pt-3 pb-3 bg-light">Bs. <?= number_format($suma_footer_formas[$forma_pago['tipo']], 2) ?></td>
                        <?php endforeach; ?>
                        <td class="text-end fw-bold text-white pt-3 pb-3" style="background-color: #198754; font-size: 1.1em;">
                            Bs. <?= number_format($suma_footer_total_general, 2) ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <script>
        function filtrar() {
            const params = new URLSearchParams();
            params.set('fecha_inicio', document.getElementById('fecha_inicio').value);
            params.set('fecha_fin', document.getElementById('fecha_fin').value);
            window.location.href = '?' + params.toString();
        }
    </script>
</body>
</html>
