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
$usuarioID_filtro = $_GET['usuarioID'] ?? '';
$empresaID_filtro = $_GET['empresaID'] ?? $_SESSION['empresaID'];
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Obtener información del rol del usuario desde sesión
$rol_usuario = $_SESSION['sesion_rol'] ?? '';

// Obtener usuarioID actual
$usuarioID_actual = $_SESSION['usuarioID'] ?? $_SESSION['sesion_usuarioID'] ?? 0;

// Construir WHERE base según el rol
$where_conditions = ["m.empresaID = ?"];
$params = [$empresaID_filtro];

// Si es RECEPCIONISTA, solo ve sus movimientos
if ($rol_usuario == 'RECEPCIONISTA') {
    $where_conditions[] = "m.usuarioID = ?";
    $params[] = $usuarioID_actual;
} else {
    // Otros roles pueden filtrar por usuario
    if (!empty($usuarioID_filtro)) {
        $where_conditions[] = "m.usuarioID = ?";
        $params[] = $usuarioID_filtro;
    }
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Obtener formas de pago disponibles
$sql_formas_pago = "SELECT fp.formapagoID, fp.tipo 
                   FROM formas_pago fp 
                   WHERE fp.empresaID = ? AND fp._estado <> 'X'
                   ORDER BY fp.tipo";
$formas_pago = $db->obtenerTodo($sql_formas_pago, [$empresaID_filtro]);

// Obtener usuarios para filtro (solo para PROPIETARIO y ADMINISTRADOR)
$usuarios = [];
if ($rol_usuario != 'RECEPCIONISTA') {
    $sql_usuarios = "SELECT u.usuarioID, u.usuario 
                    FROM usuarios u 
                    WHERE u._estado <> 'X'
                    ORDER BY u.usuario";
    $usuarios = $db->obtenerTodo($sql_usuarios);
}

// Obtener usuarios únicos que tienen movimientos para el filtro
$where_usuarios = ["m.empresaID = ?"];
$params_usuarios = [$empresaID_filtro];

if ($rol_usuario == 'RECEPCIONISTA') {
    $where_usuarios[] = "m.usuarioID = ?";
    $params_usuarios[] = $usuarioID_actual;
}

$sql_usuarios_mov = "SELECT DISTINCT u.usuarioID, u.usuario
                    FROM usuarios u
                    INNER JOIN movimientos m ON u.usuarioID = m.usuarioID
                    WHERE " . implode(" AND ", $where_usuarios) . "
                    ORDER BY u.usuario";
$usuarios_mov = $db->obtenerTodo($sql_usuarios_mov, $params_usuarios);

// Generar vista semanal basada en movimientos
$vista_semanal = [];
$fechas_rango = [];

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
    // Obtener movimientos del día
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
                            WHERE DATE(m._fec_insercion) = ? AND m.empresaID = ? AND m._estado = 'A'";
    
    $params_mov = [$fecha, $empresaID_filtro];
    
    if ($rol_usuario == 'RECEPCIONISTA') {
        $sql_movimientos_dia .= " AND m.usuarioID = ?";
        $params_mov[] = $usuarioID_actual;
    } elseif (!empty($usuarioID_filtro)) {
        $sql_movimientos_dia .= " AND m.usuarioID = ?";
        $params_mov[] = $usuarioID_filtro;
    }
    
    $sql_movimientos_dia .= " ORDER BY m._fec_insercion DESC";
    
    $movimientos_dia = $db->obtenerTodo($sql_movimientos_dia, $params_mov);
    
    // Agrupar movimientos por usuario y caja
    $agrupados = [];
    foreach ($movimientos_dia as $mov) {
        $clave_usuario = $mov['usuarioID'] . '_' . $mov['cajaID'];
        
        if (!isset($agrupados[$clave_usuario])) {
            $agrupados[$clave_usuario] = [
                'usuario' => $mov['nombre_usuario'],
                'cajaID' => $mov['cajaID'],
                'saldos' => [],
                'movimientos_count' => 0
            ];
        }
        
        // Acumular saldos por forma de pago
        $forma_pago = $mov['forma_pago'];
        if (!isset($agrupados[$clave_usuario]['saldos'][$forma_pago])) {
            $agrupados[$clave_usuario]['saldos'][$forma_pago] = 0;
        }
        
        // Sumar o restar según tipo de movimiento
        if ($mov['tipo'] == 'INGRESO') {
            $agrupados[$clave_usuario]['saldos'][$forma_pago] += $mov['monto'];
        } else {
            $agrupados[$clave_usuario]['saldos'][$forma_pago] -= $mov['monto'];
        }
        
        $agrupados[$clave_usuario]['movimientos_count']++;
    }
    
    $vista_semanal[$fecha]['movimientos'] = array_values($agrupados);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Cajas</title>
    
</head>
<body>
    <div class="container-fluid mt-4">
        <h2>Vista Semanal de Movimientos</h2>
        
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-3">
                <label for="fecha_inicio">Fecha Inicio:</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-3">
                <label for="fecha_fin">Fecha Fin:</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            
            <?php if ($rol_usuario != 'RECEPCIONISTA'): ?>
            <div class="col-md-3">
                <label for="usuarioID">Usuario:</label>
                <select id="usuarioID" name="usuarioID" class="form-control">
                    <option value="">Todos</option>
                    <?php foreach ($usuarios_mov as $usuario): ?>
                        <option value="<?= $usuario['usuarioID'] ?>" <?= $usuarioID_filtro == $usuario['usuarioID'] ? 'selected' : '' ?>>
                            <?= $usuario['usuario'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
        </div>
        
        <button onclick="filtrar()" class="btn btn-primary mb-4">Filtrar</button>
        
        <!-- Tabla semanal -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario</th>
                        <th>Caja ID</th>
                        <th>Movimientos</th>
                        <?php foreach ($formas_pago as $forma_pago): ?>
                            <th class="text-end"><?= $forma_pago['tipo'] ?></th>
                        <?php endforeach; ?>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vista_semanal as $fecha => $datos): ?>
                        <?php if (empty($datos['movimientos'])): ?>
                            <tr>
                                <td colspan="<?= 4 + count($formas_pago) ?>" class="text-center text-muted">
                                    <?= date('d/m/Y', strtotime($fecha)) ?> - Sin movimientos
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($datos['movimientos'] as $movimiento): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($fecha)) ?></td>
                                    <td><?= $movimiento['usuario'] ?></td>
                                    <td><?= $movimiento['cajaID'] ?></td>
                                    <td><?= $movimiento['movimientos_count'] ?></td>
                                    
                                    <?php 
                                    $total_fila = 0;
                                    foreach ($formas_pago as $forma_pago): 
                                        $monto = $movimiento['saldos'][$forma_pago['tipo']] ?? 0;
                                        $total_fila += $monto;
                                    ?>
                                        <td class="text-end">Bs. <?= number_format($monto, 2) ?></td>
                                    <?php endforeach; ?>
                                    
                                    <td class="text-end fw-bold">Bs. <?= number_format($total_fila, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function filtrar() {
            const params = new URLSearchParams();
            params.set('fecha_inicio', document.getElementById('fecha_inicio').value);
            params.set('fecha_fin', document.getElementById('fecha_fin').value);
            
            <?php if ($rol_usuario != 'RECEPCIONISTA'): ?>
            const usuarioID = document.getElementById('usuarioID').value;
            if (usuarioID) params.set('usuarioID', usuarioID);
            <?php endif; ?>
            
            window.location.href = '?' + params.toString();
        }
    </script>
</body>
</html>
