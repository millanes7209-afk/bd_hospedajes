<?php
// --- CORTAFUEGOS DE ERRORES ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ------------------------------

require_once("conexion.php");
require_once("funciones_caja.php");

// Activar búfer de salida para permitir redirecciones después de incluir el menú
ob_start();

// Verificar si existe la sesión de rol
if (isset($_SESSION["sesion_id_rol"])) {
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $caja_abierta_id = verificarCajaAbierta($db, $usuarioID, $_SESSION['empresaID']);
    if ($caja_abierta_id) {
        $_SESSION['caja_abierta_id'] = $caja_abierta_id;
        $caja_abierta = true;
    } else {
        $_SESSION['caja_abierta_id'] = null;
        $caja_abierta = false;
    }
    // Verificar si hay una caja abierta para el usuario actual

    $empresaID = $_SESSION['empresaID'];


    $sql_caja_abierta = "SELECT * FROM cajas WHERE estado = 'ABIERTA' AND usuarioID = ? AND empresaID = ?";
    $rs_caja_abierta = $db->obtenerTodo($sql_caja_abierta, [$usuarioID, $empresaID]);

    $caja_abierta = (count($rs_caja_abierta) > 0);
    if (!$rs_caja_abierta) {
        $rs_caja_abierta = [];
    }
    $saldos_forma_pago = [];
    if ($caja_abierta) {
        $caja_id_abierta = $rs_caja_abierta[0]['cajaID'] ?? null;

        // Obtener todas las formas de pago disponibles
        $sql_formas_pago = "SELECT tipo FROM formas_pago WHERE _estado <> 'X'";
        $rs_formas_pago = $db->obtenerTodo($sql_formas_pago);

        // Inicializar todos los saldos en 0 para cada forma de pago
        foreach ($rs_formas_pago as $forma_pago) {
            $saldos_forma_pago[$forma_pago['tipo']] = 0.00;
        }

        // Obtener sumatoria real de la tabla movimientos para esta caja puntual
        $sql_saldos = "SELECT 
                        fp.tipo AS forma_pago_tipo,
                        SUM(CASE WHEN m.tipo = 'INGRESO' THEN m.monto ELSE 0 END) AS total_ingresos,
                        SUM(CASE WHEN m.tipo = 'EGRESO' THEN m.monto ELSE 0 END) AS total_egresos
                       FROM movimientos m
                       INNER JOIN formas_pago fp ON m.formapagoID = fp.formapagoID
                       WHERE m.cajaID = ? AND m.usuarioID = ? AND m.empresaID = ? AND m._estado = 'A'
                       GROUP BY fp.tipo";

        $rs_saldo_acumulado = $db->obtenerTodo($sql_saldos, [$caja_id_abierta, $usuarioID, $empresaID]);

        // Procesar los saldos y actualizar el arreglo final
        if ($rs_saldo_acumulado) {
            foreach ($rs_saldo_acumulado as $saldo) {
                $formaPagoTipo = $saldo['forma_pago_tipo'];
                $total_ingresos = $saldo['total_ingresos'];
                $total_egresos = $saldo['total_egresos'];

                // Calcular el saldo acumulado real
                $saldo_acumulado = $total_ingresos - $total_egresos;

                // Actualizar el saldo por forma de pago en el arreglo
                $saldos_forma_pago[$formaPagoTipo] = $saldo_acumulado;
            }
        }
    }
    // Obtener empresaID desde la sesión
    $empresaID = $_SESSION['empresaID'];

    // Información de la empresa (filtrada por empresa de la sesión)
    $sql1 = "SELECT nombre, logo_agencia FROM empresa WHERE empresaID = ?";
    $rs1 = $db->obtenerTodo($sql1, array($empresaID));
    $nombre = $rs1[0]["nombre"];
    $logo_agencia = $rs1[0]["logo_agencia"];

    $dir_php = $_SERVER["PHP_SELF"];
    $cuerp = strpos($dir_php, "listado_tablas.php");

    // Determinar la ruta correcta para la imagen
    $img_path1 = '../img/' . $logo_agencia;
    $img_path2 = '../../../img/' . $logo_agencia;
    $img_path = file_exists($img_path1) ? $img_path1 : $img_path2;

    // Información del usuario y sus opciones de navegación
    $sql = "SELECT ac.*, op.opcionID, op.orden, op.contenido, gr.grupoID, gr.grupo, op.opcion 
                         FROM accesos ac
                         INNER JOIN opciones op ON ac.opcionID = op.opcionID
                         INNER JOIN grupos gr ON op.grupoID = gr.grupoID
                         WHERE ac.rolID = ?
                         AND ac._estado <> 'X'
                         AND op._estado <> 'X'
                         AND gr._estado <> 'X'
                         ORDER BY op.grupoID, op.orden";
    $rs = $db->obtenerTodo($sql, [$_SESSION["sesion_id_rol"]]);
    $nick = $_SESSION["sesion_usuario"];
} else {
    $rs = "";
    $nick = "";
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php if ($cuerp == false): ?>
        <link href='../../startbootstrap-resume-gh-pages/vendor/bootstrap/css/bootstrap.min.css' rel='stylesheet'>
        <link href='../../startbootstrap-resume-gh-pages/css/resume.min.css' rel='stylesheet'>
        <link href="../../startbootstrap-resume-gh-pages/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
        <script src='../../startbootstrap-resume-gh-pages/vendor/jquery/jquery.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js'></script>
        <script src='../../bootstrap5/js/bootstrap.bundle.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/vendor/jquery-easing/jquery.easing.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/js/resume.min.js'></script>
        <link href='../../startbootstrap-resume-gh-pages/css/metisMenu.min.css' rel='stylesheet'>
        <link href='../../startbootstrap-resume-gh-pages/css/dataTables.bootstrap.css' rel='stylesheet'>
        <link href='../../startbootstrap-resume-gh-pages/css/dataTables.responsive.css' rel='stylesheet'>
        <script src='../../startbootstrap-resume-gh-pages/js/jquery.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/js/metisMenu.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/js/jquery.dataTables.min.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/js/dataTables.responsive.js'></script>
        <script src='../../startbootstrap-resume-gh-pages/js/dataTables.bootstrap.min.js'></script>
    <?php else: ?>
        <link href='startbootstrap-resume-gh-pages/vendor/bootstrap/css/bootstrap.min.css' rel='stylesheet'>
        <link href='startbootstrap-resume-gh-pages/vendor/fontawesome-free/css/all.min.css' rel='stylesheet'>
        <link href='startbootstrap-resume-gh-pages/css/resume.min.css' rel='stylesheet'>
        <script src='startbootstrap-resume-gh-pages/vendor/jquery/jquery.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/vendor/bootstrap/js/bootstrap.bundle.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/vendor/jquery-easing/jquery.easing.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/js/resume.min.js'></script>
        <link href='startbootstrap-resume-gh-pages/css/metisMenu.min.css' rel='stylesheet'>
        <link href='startbootstrap-resume-gh-pages/css/dataTables.bootstrap.css' rel='stylesheet'>
        <link href='startbootstrap-resume-gh-pages/css/dataTables.responsive.css' rel='stylesheet'>
        <script src='startbootstrap-resume-gh-pages/js/jquery.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/js/metisMenu.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/js/jquery.dataTables.min.js'></script>
        <script src='startbootstrap-resume-gh-pages/js/dataTables.responsive.js'></script>
        <script src='startbootstrap-resume-gh-pages/js/dataTables.bootstrap.min.js'></script>
    <?php endif; ?>
    <style>
        @media (max-width: 991.98px) {
            .navbar-toggler {
                order: 1 !important;
                margin-left: 0 !important;
                margin-right: auto !important;
            }

            .navbar-brand {
                order: 2 !important;
                margin-left: auto !important;
                margin-right: 0 !important;
            }

            .navbar-collapse {
                order: 3 !important;
                width: 100% !important;
            }
        }

        @media (min-width: 992px) {
            #sideNav .navbar-toggler {
                display: block !important;
                cursor: pointer;
            }

            body:not(.menu-abierto) {
                padding-top: 60px !important;
                padding-left: 0 !important;
                transition: padding 0.3s ease;
            }

            body:not(.menu-abierto) #sideNav {
                flex-direction: row !important;
                width: 100vw !important;
                height: 60px !important;
                align-items: center;
                overflow: hidden;
            }

            body:not(.menu-abierto) #sideNav .navbar-toggler {
                order: 1 !important;
                margin-left: 0 !important;
                margin-right: auto !important;
            }

            body:not(.menu-abierto) #sideNav .navbar-brand {
                order: 2 !important;
                margin-left: auto !important;
                margin-right: 0 !important;
            }

            body:not(.menu-abierto) #sideNav .navbar-brand .d-none.d-lg-block {
                display: none !important;
            }

            body:not(.menu-abierto) #sideNav .navbar-brand .d-lg-none {
                display: block !important;
            }

            body:not(.menu-abierto) #sideNav .navbar-collapse {
                display: none !important;
            }

            body.menu-abierto {
                padding-top: 0 !important;
                padding-left: 17rem !important;
                transition: padding 0.3s ease;
            }

            body.menu-abierto #sideNav {
                width: 17rem !important;
                height: 100vh !important;
                flex-direction: column !important;
                align-items: center;
            }

            body.menu-abierto #sideNav .navbar-toggler {
                position: absolute;
                top: 10px;
                left: 10px;
                z-index: 10;
            }

            body.menu-abierto #sideNav .navbar-collapse {
                display: flex !important;
                width: 100%;
            }
        }

        /* AJUSTE DE LEGIBILIDAD SOLICITADO: TEXTO NEGRO PURO */
        table, th, td, 
        .card-header, .card-body, 
        .usuario, .saldo-info,
        h1, h2, h3, h4, h5, h6,
        .text-muted, .header-leyenda {
            color: #000000 !important;
        }

        /* --- Estilos para la Leyenda de Puntos (Contextual) --- */
        .header-leyenda {
            display: flex;
            flex-direction: column;
            font-size: 10px;
            line-height: 1.2;
            justify-content: center;
            margin: 0 20px;
            border-left: 1px solid #ddd;
            padding-left: 15px;
        }

        .leyenda-item {
            display: flex;
            align-items: center;
            white-space: nowrap;
            margin-bottom: 1px;
        }

        .leyenda-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .dot-ocupada {
            background-color: #007bff;
        }

        .dot-reservada {
            background-color: #17a2b8;
        }

        .dot-disponible {
            background-color: #28a745;
        }

        .dot-deuda {
            background-color: #dc3545;
        }

        .dot-limpieza {
            background-color: #6c757d;
        }

        .dot-mantenimiento {
            background-color: #343a40;
        }

        /* Botones de acción en header */
        .header-actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 4px;
            margin-right: 15px;
        }

        .btn-header-acc {
            padding: 4px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
        }

        .btn-header-ingreso {
            background-color: #28a745;
        }

        .btn-header-egreso {
            background-color: #fd7e14;
        }

        .btn-header-acc:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.querySelector('#sideNav .navbar-toggler');
            if (window.innerWidth >= 992) {
                if (localStorage.getItem('estadoMenuLateral') === 'abierto') {
                    document.body.classList.add('menu-abierto');
                }
            }
            if (btn) {
                btn.addEventListener('click', function (e) {
                    if (window.innerWidth >= 992) {
                        e.stopPropagation();
                        e.preventDefault();
                        document.body.classList.toggle('menu-abierto');
                        localStorage.setItem('estadoMenuLateral', document.body.classList.contains('menu-abierto') ? 'abierto' : 'cerrado');
                    }
                });
            }

            // --- CERRAR MENÚ AL SELECCIONAR OPCIÓN/PESTAÑA ---
            document.addEventListener('click', function (e) {
                const target = e.target.closest('.nav-link, .nav-tab-item');
                if (target) {
                    if (window.innerWidth >= 992) {
                        document.body.classList.remove('menu-abierto');
                        localStorage.setItem('estadoMenuLateral', 'cerrado');
                    }
                }
            });
        });
    </script>
</head>

<body>
    <?php if ($nick != ""): ?>
        <nav class='navbar navbar-expand-lg navbar-dark bg-primary fixed-top' id='sideNav'>
            <a class='navbar-brand js-scroll-trigger' href='#page-top'>
                <span class='d-block d-lg-none'>MENÚ</span>
                <span class='d-none d-lg-block'>
                    <img class='img-fluid img-profile rounded-circle mx-auto mb-2' src='<?php echo $img_path; ?>' alt=''>
                </span>
            </a>

            <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarSupportedContent'
                aria-controls='navbarSupportedContent' aria-expanded='false' aria-label='Toggle navigation'>
                <span class='navbar-toggler-icon'></span>
            </button>

            <div class='collapse navbar-collapse' id='navbarSupportedContent'>
                <ul class='navbar-nav'>
                    <?php
                    // Pre-procesar para encontrar la primera opción de cada grupo para el link directo
                    $primeras_opciones = [];
                    if (is_array($rs)) {
                        foreach ($rs as $fila) {
                            if (!isset($primeras_opciones[$fila['grupo']])) {
                                $primeras_opciones[$fila['grupo']] = $fila['contenido'];
                            }
                        }
                    }

                    $grup = "";
                    if (is_array($rs)) {
                        foreach ($rs as $fila) {
                            if ($grup != $fila["grupo"]) {
                                $url_primera = ($cuerp == false ? '../' : 'sis_segundo_2023/') . $primeras_opciones[$fila["grupo"]];
                                echo "
                                <li class='nav-item'>
                                    <a class='nav-link' href='$url_primera'>
                                        " . $fila["grupo"] . "
                                    </a>
                                </li>";
                                $grup = $fila["grupo"];
                            }
                        }
                    }
                    ?>
                    <li class='nav-item mt-4 mb-3'>
                        <a class='nav-link' href='../../selector_empresa.php'>
                            <i class="fas fa-arrow-left"></i> VOLVER A EMPRESAS
                        </a>
                    </li>
                    <li class='nav-item'>
                        <a class='nav-link' href='<?= ($cuerp == false ? "../../validar.php" : "validar.php") ?>'>
                            <i class="fas fa-sign-out-alt"></i> CERRAR SESIÓN
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class='d-flex justify-content-between align-items-center' style='padding: 15px;'>
            <div class='d-flex flex-column'>
                <h4 class='mb-0 text-muted font-weight-bold'>SISTEMA
                    <span class='text-primary'><?php echo $nombre; ?></span>
                </h4>
                <div class='usuario'>
                    &nbsp;&nbsp; USUARIO: <b><?php echo $_SESSION["sesion_usuario"]; ?></b> &nbsp;&nbsp;
                    ROL: <b><?php echo $_SESSION["sesion_rol"]; ?></b>
                </div>
            </div>

            <div class='saldo-caja d-flex align-items-center' style='margin-left: auto;'>
                <?php
                // --- ELEMENTOS CONTEXTUALES PARA MAPA DE HABITACIONES ---
                if (strpos($_SERVER['PHP_SELF'], 'habitaciones.php') !== false):
                    $boton_header_estado = ($caja_abierta) ? "" : "disabled";
                    ?>
                    <!-- Leyenda en Puntos -->
                    <div class="header-leyenda d-none d-md-flex">
                        <div class="leyenda-item"><span class="leyenda-dot dot-ocupada"></span> OCUPADA</div>
                        <div class="leyenda-item"><span class="leyenda-dot dot-reservada"></span> RESERVADA</div>
                        <div class="leyenda-item"><span class="leyenda-dot dot-disponible"></span> DISPONIBLE</div>
                        <div class="leyenda-item"><span class="leyenda-dot dot-deuda"></span> DEUDA</div>
                        <div class="leyenda-item"><span class="leyenda-dot dot-limpieza"></span> LIMPIEZA</div>
                        <div class="leyenda-item"><span class="leyenda-dot dot-mantenimiento"></span> MANTENIMIENTO</div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="header-actions">
                        <button type="button" class="btn-header-acc btn-header-ingreso" onclick="mostrarModalIngreso()"
                            <?= $boton_header_estado ?>>Otros Ingresos</button>
                        <button type="button" class="btn-header-acc btn-header-egreso" onclick="mostrarModalEgreso()"
                            <?= $boton_header_estado ?>>Registrar Egreso</button>
                    </div>
                <?php endif; ?>

                <?php if ($_SESSION["sesion_rol"] == 'RECEPCIONISTA' || $_SESSION["sesion_rol"] == 'ADMINISTRADOR'): ?>
                    <?php if ($caja_abierta): ?>
                        <button type='button' class='btn btn-danger' data-toggle='modal' data-target='#modalCerrarCaja'
                            style='margin-right: 15px;'>
                            <i class='fas fa-lock'></i> Cerrar Caja
                        </button>
                    <?php else: ?>
                        <form action='<?php echo ($cuerp == 'false') ? 'procesar_caja.php' : '../../procesar_caja.php'; ?>'
                            method='post' style='margin-right: 15px;'>
                            <input type='hidden' name='accion' value='abrir'>
                            <button type='submit' class='btn btn-success'>
                                <i class='fas fa-lock-open'></i> Abrir Caja
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <div id='saldo-acumulado' class='saldo-info' style='font-size: 18px;'>
                    <?php foreach ($saldos_forma_pago as $formaPagoTipo => $saldo): ?>
                        (<?php echo $formaPagoTipo; ?>): Bs. <?php echo number_format($saldo, 2); ?><br>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div id="miModal" class="modal">
        <div class="modal-contenido">
            <div class="modal-header"><span class="cerrar">&times;</span>
                <h2>Notificación</h2>
            </div>
            <div class="modal-body">
                <p id="modalMensaje">Aquí irá el mensaje de la notificación.</p>
            </div>
            <div class="modal-footer"><button id="facturaEmitidaBtn">Factura Emitida</button><button
                    id="posponerBtn">Posponer</button></div>
        </div>
    </div>

    <?php
    $saldos_json = json_encode([]);
    $total_general_json = json_encode(0);
    $fecha_apertura_json = json_encode("");

    if (isset($caja_abierta) && $caja_abierta && ($_SESSION["sesion_rol"] == 'RECEPCIONISTA' || $_SESSION["sesion_rol"] == 'ADMINISTRADOR')) {
        $saldos_modal = array_filter($saldos_forma_pago, function ($saldo) {
            return $saldo > 0;
        });
        $saldos_json = json_encode($saldos_modal);
        $total_general_json = json_encode(array_sum($saldos_modal));
        $fecha_apertura_iso = isset($rs_caja_abierta[0]['fecha_apertura']) ? str_replace(' ', 'T', $rs_caja_abierta[0]['fecha_apertura']) : '';
        $fecha_apertura_json = json_encode($fecha_apertura_iso);
        include_once('modal_cerrar_caja.php');
    }
    ?>

    <script>
        var saldosModal = <?php echo $saldos_json ?? '{}'; ?>;
        var totalGeneral = <?php echo $total_general_json ?? '0'; ?>;
        var fechaApertura = <?php echo $fecha_apertura_json ?? '""'; ?>;
    </script>

    <?php
    // =========================================================================
// INYECCIÓN AUTOMÁTICA DE PESTAÑAS (TABS)
// =========================================================================
    if (isset($_SESSION["sesion_id_rol"]) && is_array($rs)) {
        $dir_actual = basename($_SERVER['PHP_SELF']);
        $grupo_actual_id = null;
        foreach ($rs as $fila) {
            if (strpos($fila['contenido'], $dir_actual) !== false) {
                $grupo_actual_id = $fila['grupoID'];
                break;
            }
        }

        if ($grupo_actual_id) {
            $prefix = ($cuerp == false) ? '../../' : '';
            echo '<link rel="stylesheet" href="' . $prefix . 'privada/hospedajes/css/tabs.css">';
            echo '<div class="contenedor-tabs" style="margin: 0 15px 20px 15px; flex-wrap: wrap; display: flex;">';
            foreach ($rs as $p) {
                if ($p['grupoID'] == $grupo_actual_id) {
                    $es_activa = (strpos($p['contenido'], $dir_actual) !== false) ? 'active' : '';

                    // Normalizar ruta para evitar SyntaxError por contra-barras (\) en Windows
                    $ruta_limpia = str_replace('\\', '/', $p['contenido']);
                    $ruta_final = ($cuerp == false ? '../' : 'sis_segundo_2023/') . $ruta_limpia;

                    echo "<a href='$ruta_final' class='nav-tab-item $es_activa'>" . htmlspecialchars($p['opcion']) . "</a>";
                }
            }
            echo '</div>';
        }
    }
    ?>
</body>

</html>