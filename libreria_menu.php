<?php
// --- CORTAFUEGOS DE ERRORES ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ------------------------------

require_once(__DIR__ . "/privada/seguridad/seguridad.php");
require_once("conexion.php");
require_once("funciones_caja.php");

// Activar búfer de salida para permitir redirecciones después de incluir el menú
ob_start();

// Verificar si existe la sesión de rol
if (isset($_SESSION["sesion_id_rol"])) {
    $usuarioID = $_SESSION['sesion_id_usuario'];
    $rolID = $_SESSION["sesion_id_rol"];
    $empresaID = $_SESSION['empresaID'] ?? null;
    $is_global = ($empresaID === null || $empresaID == 0);

    // --- LÓGICA DE SUCURSAL ---
    if (!$is_global) {
        $caja_abierta_id = verificarCajaAbierta($db, $usuarioID, $empresaID);
        $_SESSION['caja_abierta_id'] = $caja_abierta_id ?: null;
        $caja_abierta = (bool) $caja_abierta_id;

        $saldos_forma_pago = [];
        if ($caja_abierta) {
            // Obtener todas las formas de pago disponibles
            $sql_formas_pago = "SELECT tipo FROM formas_pago WHERE _estado <> 'X'";
            $rs_formas_pago = $db->obtenerTodo($sql_formas_pago);
            foreach ($rs_formas_pago as $forma_pago)
                $saldos_forma_pago[$forma_pago['tipo']] = 0.00;

            // Obtener sumatoria real de movimientos desde la vista unificada (ahora en PHP)
            $vista = $db->getVistaMovimientos();
            $sql_saldos = "SELECT forma_pago AS forma_pago_tipo,
                            SUM(CASE WHEN tipo = 'INGRESO' THEN monto ELSE 0 END) AS total_ingresos,
                            SUM(CASE WHEN tipo = 'EGRESO' THEN monto ELSE 0 END) AS total_egresos
                           FROM $vista as t
                           WHERE cajaID = ? AND empresaID = ? AND _estado <> 'X'
                           GROUP BY forma_pago";
            $rs_saldo_acumulado = $db->obtenerTodo($sql_saldos, [$caja_abierta_id, $empresaID]);

            if ($rs_saldo_acumulado) {
                foreach ($rs_saldo_acumulado as $saldo) {
                    $saldos_forma_pago[$saldo['forma_pago_tipo']] = $saldo['total_ingresos'] - $saldo['total_egresos'];
                }
            }
        }

        // Información de la empresa y tematización
        $sql1 = "SELECT nombre, logo_agencia, color_primario, color_secundario FROM empresa WHERE empresaID = ?";
        $rs1 = $db->obtenerTodo($sql1, array($empresaID));
        $nombre = $rs1[0]["nombre"] ?? "SISTEMA GLOBAL";
        $logo_agencia = $rs1[0]["logo_agencia"] ?? "default.png";

        // Si es global (selector de empresa), usamos colores neutros elegantes. Si no, colores de empresa.
        if ($is_global) {
            $color_primario = "#212529"; // Gris muy oscuro neutro
            $color_secundario = "#6c757d"; // Gris claro
        } else {
            $color_primario = !empty($rs1[0]["color_primario"]) ? $rs1[0]["color_primario"] : "#bd5d38"; // Ladrillo
            $color_secundario = !empty($rs1[0]["color_secundario"]) ? $rs1[0]["color_secundario"] : "#ffffff";
        }

        // SQL PARA MENÚ DE SUCURSAL (Filtrado por funcionalidades pagadas)
        // Ocultamos la funcionalidad 5 (SISTEMA) y mostramos solo lo pagado
        $sql = "SELECT ac.*, op.opcionID, op.orden, op.contenido, gr.grupoID, gr.grupo, op.opcion 
                FROM accesos ac
                INNER JOIN opciones op ON ac.opcionID = op.opcionID
                INNER JOIN grupos gr ON op.grupoID = gr.grupoID
                LEFT JOIN empresa_funcionalidades ef ON (op.funcionalidadID = ef.funcionalidadID AND ef.empresaID = ? AND ef.estado = 'ACTIVO')
                WHERE ac.rolID = ?
                AND op.funcionalidadID <> 5
                AND (op.funcionalidadID IS NULL OR ef.empresafuncionID IS NOT NULL)
                AND ac._estado <> 'X' AND op._estado <> 'X' AND gr._estado <> 'X'
                ORDER BY op.grupoID, op.orden";
        $params = [$empresaID, $rolID];
    }
    // --- LÓGICA PANEL GLOBAL ---
    else {
        $nombre = "PANEL MAESTRO DE SISTEMA";
        $logo_agencia = "logo_global.png"; // Icono genérico
        $caja_abierta = false;
        $saldos_forma_pago = [];

        // SQL PARA MENÚ GLOBAL (Solo funcionalidad 5: SISTEMA)
        $sql = "SELECT ac.*, op.opcionID, op.orden, op.contenido, gr.grupoID, gr.grupo, op.opcion 
                FROM accesos ac
                INNER JOIN opciones op ON ac.opcionID = op.opcionID
                INNER JOIN grupos gr ON op.grupoID = gr.grupoID
                WHERE ac.rolID = ?
                AND op.funcionalidadID = 5
                AND ac._estado <> 'X' AND op._estado <> 'X' AND gr._estado <> 'X'
                ORDER BY op.grupoID, op.orden";
        $params = [$rolID];
    }

    $rs = $db->obtenerTodo($sql, $params);

    // --- NUEVO: DETECCIÓN DE MÓDULOS ACTIVOS PARA LA EMPRESA ---
    $modulos_activos = [];
    if (!$is_global) {
        $sql_mods = "SELECT funcionalidadID FROM empresa_funcionalidades WHERE empresaID = ? AND estado = 'ACTIVO' AND _estado <> 'X'";
        $rs_mods = $db->obtenerTodo($sql_mods, [$empresaID]);
        $modulos_activos = array_column($rs_mods, 'funcionalidadID');
    }
    // Flag para el módulo de baños premium (MÓDULO INDEPENDIENTE ID 4)
    $tieneModuloBanos = in_array(4, $modulos_activos);
    // -----------------------------------------------------------

    $nick = $_SESSION["sesion_usuario"];
    $dir_php = $_SERVER["PHP_SELF"];
    $cuerp = strpos($dir_php, "listado_tablas.php");

    // Ruta de imagen (Detección de profundidad de carpetas interna al proyecto)
    // Buscamos cuántos niveles subir hasta encontrar la carpeta img/ propia del proyecto
    $img_path = "";
    $prefijo = "";
    for ($i = 0; $i < 5; $i++) {
        if (file_exists($prefijo . 'img/' . $logo_agencia)) {
            $img_path = $prefijo . 'img/' . $logo_agencia;
            break;
        }
        $prefijo .= "../";
    }

    // Si no se encuentra, usar default
    if (empty($img_path))
        $img_path = $prefijo . "img/default.png";

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
        table,
        th,
        td,
        .card-header,
        .card-body,
        .usuario,
        .saldo-info,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .text-muted,
        .header-leyenda {
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

            // --- ANTI DOBLE ENVÍO GLOBAL: Deshabilita el botón submit al primer clic ---
            document.addEventListener('submit', function (e) {
                const form = e.target;
                const submitBtn = form.querySelector('[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                    submitBtn.style.cursor = 'not-allowed';
                    // Reactivar después de 8 segundos por si el servidor tarda o falla
                    setTimeout(function () {
                        submitBtn.disabled = false;
                        submitBtn.style.opacity = '';
                        submitBtn.style.cursor = '';
                    }, 8000);
                }
            }, true);

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

    <!-- TEMATIZACIÓN DINÁMICA POR EMPRESA -->
    <?php if (isset($color_primario) && isset($color_secundario)): ?>
        <style>
            :root {
                --color-primario:
                    <?php echo $color_primario; ?>
                ;
                --color-secundario:
                    <?php echo $color_secundario; ?>
                ;
            }

            /* Modificar el Navbar / Menú Lateral */
            #sideNav.bg-primary {
                background-color: var(--color-primario) !important;
            }

            /* Efecto Hover en los enlaces del menú usando el color secundario */
            #sideNav .nav-link:hover {
                color: var(--color-secundario) !important;
                opacity: 0.9;
            }

            /* Si quieres que el título SISTEMA EMPRESA resalte con el primario */
            h4 .text-primary {
                color: var(--color-primario) !important;
            }
        </style>
    <?php endif; ?>
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
                <ul class="navbar-nav">
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
                    <li class="nav-item">
                        <a href="../../selector_rol.php?manual=1" class="nav-link">
                            <i class="fas fa-user-tag me-2"></i> Cambiar Rol
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../validar.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
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

                        <!-- BOTONES DE BAÑOS MOVIDOS AQUÍ (Solo si tiene módulo Premium ID 6) -->
                        <?php if ($tieneModuloBanos): ?>
                            <div class="d-flex gap-1 ms-3 border-start ps-3">
                                <button type="button" class="btn btn-xs btn-primary py-0 px-2 fw-bold"
                                    onclick="mostrarModalBano('INGRESO')" style="font-size: 10px; height: 20px;">+ BAÑO</button>
                                <button type="button" class="btn btn-xs btn-danger py-0 px-2 fw-bold"
                                    onclick="mostrarModalBano('EGRESO')" style="font-size: 10px; height: 20px;">- BAÑO</button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Botón de Agrupación y Novedades -->
                    <div class="header-filter mx-3 d-flex flex-column gap-2" style="row-gap: 4px;">
                        <?php if (isset($_GET['orden']) && $_GET['orden'] == 'tipo'): ?>
                            <a href="habitaciones.php" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="fas fa-sort-numeric-down"></i> VER GENERAL
                            </a>
                        <?php else: ?>
                            <a href="habitaciones.php?orden=tipo" class="btn btn-sm btn-outline-primary fw-bold">
                                <i class="fas fa-layer-group"></i> VER POR TIPO
                            </a>
                        <?php endif; ?>

                        <!-- Botón de Libreta de Turno -->
                        <button type="button" class="btn btn-sm btn-outline-secondary fw-bold" id="btnNovedades"
                            onclick="abrirNovedades()">
                            <i class="fas fa-book"></i> NOTAS <span id="badgeNovedades"
                                class="badge bg-danger rounded-pill d-none">0</span>
                        </button>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="header-actions d-flex align-items-center">
                        <button type="button" class="btn-header-acc btn-header-ingreso" onclick="mostrarModalIngreso()"
                            title="Ingreso que no es hospedaje" <?= $boton_header_estado ?>>Otros Ingresos</button>
                        <button type="button" class="btn-header-acc btn-header-egreso" onclick="mostrarModalEgreso()"
                            title="Gasto de caja" <?= $boton_header_estado ?>>Registrar Egreso</button>
                    </div>

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

    <!-- MODAL LIBRETA DE NOVEDADES -->
    <div class="modal fade" id="modalNovedades" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold"><i class="fas fa-book-open"></i> Bitácora de Turno</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"
                        style="background:none; border:none; font-size:1.5rem;">&times;</button>
                </div>
                <div class="modal-body bg-light">
                    <!-- Formulario para nueva nota -->
                    <form id="formNuevaNota" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" id="txtNotaMensaje"
                                placeholder="Anotar pendiente para el siguiente turno..." required>
                            <button class="btn btn-dark" type="submit">Anotar</button>
                        </div>
                        <div id="msgNotaError" class="text-danger small mt-1 d-none"></div>
                    </form>

                    <h6 class="fw-bold text-muted border-bottom pb-2 mb-3">Pendientes Actuales</h6>
                    <ul class="list-group" id="listaNovedades">
                        <!-- Las notas se inyectan por AJAX -->
                        <li class="list-group-item text-center text-muted border-0 bg-transparent">Cargando...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- LÓGICA AJAX DE NOVEDADES -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            if (document.getElementById('btnNovedades')) {
                cargarNovedades();
                // Refrescar cada minuto en silencio
                setInterval(cargarNovedades, 60000);
            }
        });

        let notasPendientes = [];

        function cargarNovedades() {
            fetch('ajax_notificaciones.php?accion=listar')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'ok') {
                        notasPendientes = res.data;
                        actualizarBotonNovedades();
                        renderizarListaNovedades();
                    }
                });
        }

        function actualizarBotonNovedades() {
            const btn = document.getElementById('btnNovedades');
            const badge = document.getElementById('badgeNovedades');
            if (notasPendientes.length > 0) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-danger', 'text-white');
                badge.innerText = notasPendientes.length;
                badge.classList.remove('d-none');
            } else {
                btn.classList.remove('btn-danger', 'text-white');
                btn.classList.add('btn-outline-secondary');
                badge.classList.add('d-none');
            }
        }

        function renderizarListaNovedades() {
            const lista = document.getElementById('listaNovedades');
            if (!lista) return;
            lista.innerHTML = '';
            if (notasPendientes.length === 0) {
                lista.innerHTML = '<li class="list-group-item text-center text-muted border-0 bg-transparent"><i class="fas fa-check-circle text-success fs-4 mb-2 d-block"></i> Todo está al día</li>';
                return;
            }

            notasPendientes.forEach(nota => {
                const li = document.createElement('li');
                li.className = 'list-group-item d-flex justify-content-between align-items-start shadow-sm mb-2 rounded border-start border-warning border-4';
                li.innerHTML = `
                <div class="ms-2 me-auto">
                    <div class="fw-bold">${nota.mensaje}</div>
                    <small class="text-muted"><i class="fas fa-user-edit"></i> ${nota.autor} &nbsp;|&nbsp; <i class="fas fa-clock"></i> ${nota.hora} (${nota.dia})</small>
                </div>
                <button class="btn btn-sm btn-outline-secondary border-0" onclick="completarNota(${nota.notificacionID})" title="Marcar como completado">
                    <i class="far fa-square fs-5"></i>
                </button>
            `;
                lista.appendChild(li);
            });
        }

        function abrirNovedades() {
            cargarNovedades();
            let myModal = new bootstrap.Modal(document.getElementById('modalNovedades'));
            myModal.show();
        }

        function completarNota(id) {
            let fd = new FormData();
            fd.append('accion', 'completar');
            fd.append('notificacionID', id);

            fetch('ajax_notificaciones.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'ok') {
                        cargarNovedades();
                    } else {
                        mostrarError(res.message);
                    }
                });
        }

        function mostrarError(msg) {
            const errDiv = document.getElementById('msgNotaError');
            errDiv.innerText = msg;
            errDiv.classList.remove('d-none');
            setTimeout(() => errDiv.classList.add('d-none'), 4000);
        }

        // Interceptar envío de nueva nota
        const formNota = document.getElementById('formNuevaNota');
        if (formNota) {
            formNota.addEventListener('submit', function (e) {
                e.preventDefault();
                const input = document.getElementById('txtNotaMensaje');
                let msj = input.value;
                if (!msj) return;

                let fd = new FormData();
                fd.append('accion', 'guardar');
                fd.append('mensaje', msj);

                fetch('ajax_notificaciones.php', { method: 'POST', body: fd })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status === 'ok') {
                            input.value = '';
                            cargarNovedades();
                        } else {
                            mostrarError(res.message);
                        }
                    });
            });
    }
    </script>

    <?php
    $saldos_json = json_encode([]);
    $total_general_json = json_encode(0);
    $fecha_apertura_json = json_encode("");

    if (isset($caja_abierta) && $caja_abierta && ($_SESSION["sesion_rol"] == 'RECEPCIONISTA' || $_SESSION["sesion_rol"] == 'ADMINISTRADOR') && strpos($_SERVER['PHP_SELF'], 'habitaciones.php') !== false) {
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