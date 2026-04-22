<?php
session_start();
require_once("../../conexion.php");
require_once("../../libreria_menu.php");

// Verificar si el usuario está logueado
if (!isset($_SESSION['sesion_usuario'])) {
    header("Location: ../../index.php");
    exit();
}

// Obtener roles y parámetros de filtro
$rol_usuario = $_SESSION['sesion_rol'] ?? '';
$empresaID_filtro = $_SESSION['empresaID'];
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$usuarioID_filtro = $_GET['usuarioID'] ?? '';

// Obtener usuarioID actual
$usuarioID_actual = $_SESSION['sesion_id_usuario'] ?? $_SESSION['usuarioID'] ?? 0;

// Obtener usuarios para filtro si tiene privilegios
$usuarios_mov = [];
if ($rol_usuario === 'PROPIETARIO' || $rol_usuario === 'ADMINISTRADOR') {
    $sql_usuarios = "SELECT usuarioID, usuario FROM usuarios WHERE _estado <> 'X' ORDER BY usuario";
    $usuarios_mov = $db->obtenerTodo($sql_usuarios);
}

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
    // Armar consulta según el rol (Filtrar por el RESPONSABLE DEL CIERRE)
    if ($rol_usuario === 'RECEPCIONISTA') {
        $where_user = "AND cc._usuario = ?";
        $params_mov = [$fecha, $empresaID_filtro, $usuarioID_actual];
    } else {
        if (!empty($usuarioID_filtro)) {
            $where_user = "AND cc._usuario = ?";
            $params_mov = [$fecha, $empresaID_filtro, $usuarioID_filtro];
        } else {
            $where_user = "";
            $params_mov = [$fecha, $empresaID_filtro];
        }
    }

    $sql_movimientos_dia = "SELECT 
                              cc.monto,
                              'INGRESO' as mov_tipo, 
                              u.usuario as nombre_usuario,
                              fp.tipo as forma_pago,
                              cc.cajaID,
                              c.fecha_apertura
                            FROM cierre_cajas cc
                            INNER JOIN cajas c ON cc.cajaID = c.cajaID
                            INNER JOIN usuarios u ON c.usuarioID = u.usuarioID
                            INNER JOIN formas_pago fp ON cc.formapagoID = fp.formapagoID
                            WHERE DATE(c.fecha_cierre) = ? 
                              AND c.empresaID = ? 
                              AND c.estado = 'CERRADA'
                              $where_user
                            ORDER BY c.fecha_cierre DESC";

    $movimientos_dia = $db->obtenerTodo($sql_movimientos_dia, $params_mov);

    // Agrupar movimientos por usuario y caja
    $agrupados = [];
    foreach ($movimientos_dia as $mov) {
        $clave = $mov['cajaID'];

        if (!isset($agrupados[$clave])) {
            $agrupados[$clave] = [
                'usuario' => $mov['nombre_usuario'],
                'fecha_apertura' => $mov['fecha_apertura'],
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
        if ($mov['mov_tipo'] == 'INGRESO') {
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
</head>
<style>
    thead {
        color: black;
        background: #b5b5b5;
    }

    .card {
        margin: 20px;
    }
</style>

<body>
    <div class="container-fluid mt-4 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">CAJAS CERRADAS</h2>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label for="fecha_inicio">Fecha Inicio:</label>
                                <input type="date" id="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin">Fecha Fin:</label>
                                <input type="date" id="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
                            </div>
                            <?php if ($rol_usuario === 'PROPIETARIO' || $rol_usuario === 'ADMINISTRADOR'): ?>
                                <div class="col-md-3">
                                    <label for="usuarioID">Usuario:</label>
                                    <select id="usuarioID" class="form-control">
                                        <option value="">Todos los usuarios</option>
                                        <?php foreach ($usuarios_mov as $usr): ?>
                                            <option value="<?= $usr['usuarioID'] ?>" <?= $usuarioID_filtro == $usr['usuarioID'] ? 'selected' : '' ?>>
                                                <?= $usr['usuario'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            <div class="col-md-3 d-flex align-items-end">
                                <button onclick="filtrar()" class="btn btn-secondary w-100"><i
                                        class="fas fa-filter"></i> Filtrar</button>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-striped table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center">Fecha Apertura</th>
                                        <th class="text-center">Usuario</th>
                                        <?php foreach ($formas_pago as $forma_pago): ?>
                                            <th class="text-center"><?= $forma_pago['tipo'] ?></th>
                                        <?php endforeach; ?>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $hayRegistros = false;
                                    foreach ($vista_semanal as $fecha => $datos): ?>
                                        <?php if (empty($datos['movimientos'])): ?>
                                            <tr>
                                                <td colspan="<?= 2 + count($formas_pago) + 1 ?>"
                                                    class="text-center text-muted fst-italic">
                                                    <?= date('d/m/Y', strtotime($fecha)) ?> - Sin cajas cerradas
                                                </td>
                                            </tr>
                                        <?php else:
                                            $hayRegistros = true;
                                            foreach ($datos['movimientos'] as $movimiento): ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <?= date('d/m/Y', strtotime($movimiento['fecha_apertura'])) ?><br>
                                                        <small class="text-muted"><?= date('H:i', strtotime($movimiento['fecha_apertura'])) ?></small>
                                                    </td>
                                                    <td class="text-center align-middle"><?= $movimiento['usuario'] ?></td>

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
                                                    <td class="text-end align-middle fw-bold">Bs.
                                                        <?= number_format($total_fila, 2) ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>

                                <?php if ($hayRegistros): ?>
                                    <tfoot class="bg-light fw-bold" style="border-top: 2px solid #ccc;">
                                        <tr>
                                            <td colspan="2" class="text-end">Total:</td>
                                            <?php foreach ($formas_pago as $forma_pago): ?>
                                                <td class="text-center">Bs.
                                                    <?= number_format($suma_footer_formas[$forma_pago['tipo']], 2) ?>
                                                </td>
                                            <?php endforeach; ?>
                                            <td class="text-end">
                                                Bs. <?= number_format($suma_footer_total_general, 2) ?>
                                            </td>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filtrar() {
            const params = new URLSearchParams();
            params.set('fecha_inicio', document.getElementById('fecha_inicio').value);
            params.set('fecha_fin', document.getElementById('fecha_fin').value);

            const usuarioID = document.getElementById('usuarioID');
            if (usuarioID) {
                params.set('usuarioID', usuarioID.value);
            }
            window.location.href = '?' + params.toString();
        }
    </script>
</body>

</html>