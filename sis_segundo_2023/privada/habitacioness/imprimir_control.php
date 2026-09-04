<?php
session_start();
require_once("../../conexion.php");

// Proteger la vista
if (!isset($_SESSION["sesion_id_usuario"])) {
    header("Location: ../../index.php");
    exit;
}

$empresaID = $_SESSION['empresaID'];

// Consulta SQL idéntica a la de habitaciones.php
$sql = "SELECT  thab.tipohabitacionID, hab.habitacionID, hab.bano, hab.tv, hab.ventilador, 
                thab.nombre, thab.precio, hab.estado as estado, hab.numero as numero, 
                hab.descripcion as descripcion,
                hos.hospedajeID as hospedaje_activo_id,
                hos.checkin as checkin_activo,
                hos.checkout as checkout_activo,
                hos.precio_diario as precio_pactado,
                hos.monto as monto_total_pagado,
                hos.observaciones as observaciones_activo,
                cue.codigo as cuenta_codigo,
                (SELECT GROUP_CONCAT(CONCAT(c.nombres, ' ', c.apellido1) SEPARATOR ', ')
                 FROM hospedajes_clientes hc 
                 JOIN clientes c ON hc.clienteID = c.clienteID 
                 WHERE hc.hospedajeID = hos.hospedajeID 
                 AND hc._estado <> 'X' AND c._estado <> 'X') AS cliente_activo
        FROM    habitaciones hab
        JOIN    tipo_habitaciones thab ON hab.tipohabitacionID = thab.tipohabitacionID
        LEFT JOIN hospedajes hos ON hab.habitacionID = hos.habitacionID 
                 AND hos.empresaID = ? 
                 AND hos.estado = 'ACTIVO' 
                 AND hos._estado <> 'X'
        LEFT JOIN ingresos ing ON hos.ingresoID = ing.ingresoID
        LEFT JOIN cuentas cue ON ing.cuentaID = cue.cuentaID
        WHERE   thab._estado <> 'X'
        AND     hab._estado <> 'X'
        AND     hab.empresaID = ?
        ORDER BY hab.numero ASC";

$rs = $db->obtenerTodo($sql, array($empresaID, $empresaID));

// Procesar y sincronizar estados de habitaciones antes del reporte
$habitaciones = [];
if ($rs) {
    foreach ($rs as $habitacion) {
        if (!empty($habitacion['hospedaje_activo_id']) && $habitacion['estado'] !== 'OCUPADA') {
            $habitacion['estado'] = 'OCUPADA';
            $db->ejecutar("UPDATE habitaciones SET estado = 'OCUPADA' WHERE habitacionID = ?", [$habitacion['habitacionID']]);
        } else if (in_array($habitacion['estado'], ['OCUPADA', 'DEUDA']) && empty($habitacion['hospedaje_activo_id'])) {
            $habitacion['estado'] = 'LIMPIEZA';
            $db->ejecutar("UPDATE habitaciones SET estado = 'LIMPIEZA' WHERE habitacionID = ? AND empresaID = ?", [$habitacion['habitacionID'], $empresaID]);
        }

        $habitacion['dias_deuda'] = 0;
        if (($habitacion['estado'] === 'OCUPADA' || $habitacion['estado'] === 'DEUDA') && !empty($habitacion['checkout_activo'])) {
            $now_stamp = time();
            if (strtotime($habitacion['checkout_activo']) < $now_stamp) {
                if ($habitacion['estado'] === 'OCUPADA') {
                    $habitacion['estado'] = 'DEUDA';
                    $db->ejecutar("UPDATE habitaciones SET estado = 'DEUDA' WHERE habitacionID = ? AND empresaID = ?", [$habitacion['habitacionID'], $empresaID]);
                }

                $checkout_obj = new DateTime($habitacion['checkout_activo']);
                $ahora_obj = new DateTime();
                $dias_cobro = 1;
                $iter_date_limite = clone $checkout_obj;
                $iter_date_limite->modify('+1 day');
                $iter_date_limite->setTime(13, 0, 0);
                while ($iter_date_limite <= $ahora_obj) {
                    $dias_cobro++;
                    $iter_date_limite->modify('+1 day');
                }
                $habitacion['dias_deuda'] = $dias_cobro;
            }
        }
        $habitaciones[] = $habitacion;
    }
}

// Agrupar habitaciones por Piso (Nombres solicitados: PLANTA BAJA, PRIMERA PLANTA, SEGUNDA PLANTA)
$habitaciones_por_piso = [];
foreach ($habitaciones as $hab) {
    $numero = $hab['numero'];
    $piso = (int) ($numero / 100);
    if ($piso == 1) {
        $piso_nombre = "PLANTA BAJA";
    } elseif ($piso == 2) {
        $piso_nombre = "PRIMERA PLANTA";
    } elseif ($piso == 3) {
        $piso_nombre = "SEGUNDA PLANTA";
    } else {
        $piso_nombre = "PISO " . $piso;
    }
    $habitaciones_por_piso[$piso_nombre][] = $hab;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Control Diario - Impresión Horizontal</title>
    <style>
        /* Estilos base para visualización de hoja simulada en pantalla */
        body {
            font-family: Arial, sans-serif;
            color: #000;
            background-color: #767676;
            /* Fondo gris para simular hoja en pantalla */
            margin: 0;
            padding: 20px;
            font-size: 10px;
            line-height: 1.15;
        }

        /* Controles de visualización superior */
        .no-print-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            margin: 0 auto 20px auto;
            max-width: 33cm;
            /* Alineado con el ancho de la hoja */
            border: 1px solid #dee2e6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        .btn-print {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 12px;
        }

        .btn-print:hover {
            background-color: #218838;
        }

        /* Simulación de la hoja física en pantalla */
        .page-document {
            background: #fff;
            width: 21.6cm;
            /* Oficio portrait width de ~21.6cm */
            min-height: 33cm;
            /* Oficio portrait height de ~33cm */
            margin: 0 auto;
            padding: 0.3cm;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            box-sizing: border-box;
            border-radius: 2px;
            position: relative;
        }

        /* Encabezado */
        .headers-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #000;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            flex-grow: 1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-meta {
            font-size: 11px;
            font-weight: bold;
            white-space: nowrap;
        }

        /* Contenedor Superior (Refrescos y Caja lado a lado) */
        .top-control-section {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 25px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .control-box {
            flex: 1;
            max-width: 48%;
        }

        .control-box-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
        }

        /* Tablas compactas */
        .table-compact {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .table-compact th,
        .table-compact td {
            border: 1px solid #000;
            padding: 3.5px 6px;
            text-align: left;
        }

        .table-compact th {
            background-color: #f1f3f5;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .col-prod {
            width: 46%;
        }

        .col-cant {
            width: 18%;
            text-align: center;
        }

        /* Campos de arqueo de caja */
        .caja-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .caja-line span {
            font-weight: bold;
        }

        .caja-line-fill {
            flex-grow: 1;
            border-bottom: 1px dotted #000;
            margin-left: 6px;
        }

        .caja-notes-box {
            border: 1px solid #000;
            min-height: 52px;
            margin-top: 8px;
            padding: 6px;
            font-size: 9px;
            box-sizing: border-box;
        }

        /* Estructura de Habitaciones */
        .floor-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .floor-title {
            font-size: 11px;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 2px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        /* 5 Columnas para formato vertical Oficio */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 4px;
        }

        /* Bloques de habitación compactos */
        .room-card {
            border: 1px solid #000;
            padding: 3px;
            min-height: 75px;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: #fff;
            box-sizing: border-box;
            page-break-inside: avoid;
        }

        .room-deuda {
            border: 2px solid #ff0000 !important;
            background-color: #ff6666 !important;
            color: #ff0000 !important;
        }

        .room-deuda * {
            color: #ff0000 !important;
        }

        .room-card-header {
            font-weight: bold;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            margin-bottom: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .room-card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 2.5px;
        }

        /* Estilo de fila simulada para escritura en columna */
        .room-data-line {
            display: flex;
            align-items: flex-end;
            font-size: 10px;
            height: 10px;
        }

        .room-data-line-label {
            font-weight: bold;
            margin-right: 4px;
            white-space: nowrap;
        }

        .room-data-line-fill {
            flex-grow: 1;
            border-bottom: 1px dotted #000;
            height: 1px;
            margin-bottom: 2px;
        }

        .room-status-watermark {
            position: absolute;
            font-size: 32px;
            font-weight: 900;
            opacity: 0.1;
            top: 55%;
            left: 50%;
            transform: translate(-50%, -50%);
            pointer-events: none;
            color: #000;
        }

        .room-status-watermark.strong-watermark {
            opacity: 0.8 !important;
            font-size: 40px;
        }

        .room-status-deuda-text {
            font-size: 12px;
            font-weight: bold;
            color: #ff0000;
            white-space: nowrap;
            background: #fff;
            padding: 1px 3px;
            border: 1px dashed #ff0000;
            margin-left: auto;
        }

        /* Optimizaciones para imprimir físico */
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
            }

            .no-print-bar {
                display: none !important;
            }

            .page-document {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .table-compact th {
                background-color: transparent !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .rooms-grid {
                grid-template-columns: repeat(5, 1fr) !important;
            }

            .caja-notes-box {
                border-color: #000 !important;
            }

            .btn-edit {
                display: none !important;
            }
        }

        @page {
            size: legal portrait;
            margin: 0.5cm;
        }
    </style>
</head>

<?php
// Cargar datos de productos y saldos de caja
$empresaID = $_SESSION['empresaID'] ?? null;

$productos = [];
$saldo_efectivo = 0.00;
$saldo_qr = 0.00;

if ($empresaID) {
    // 1. Productos y su stock actual
    $sql_productos = "SELECT productoID, nombre, medida, stock FROM productos WHERE empresaID = ? AND _estado = 'A' AND stock != 0 ORDER BY nombre";
    $productos = $db->obtenerTodo($sql_productos, [$empresaID]);
    if (!is_array($productos))
        $productos = [];

    // 2. Saldos de caja actual (usando las columnas reales de la DB)
    $sql_caja = "SELECT saldo_efectivo, saldo_qr, saldo_total 
                FROM caja_tienda 
                WHERE empresaID = ? AND _estado = 'A' 
                ORDER BY caja_tiendaID DESC 
                LIMIT 1";
    $res_caja = $db->obtenerFila($sql_caja, [$empresaID]);
    if ($res_caja) {
        $saldo_efectivo = floatval($res_caja['saldo_efectivo']);
        $saldo_qr = floatval($res_caja['saldo_qr']);
    }
}
?>

<body>

    <!-- Barra Superior de Control No Imprimible -->
    <div class="no-print-bar">
        <span style="font-size:12px; font-weight:bold; color:#333;">Vista Previa de la Hoja (Tamaño Oficio
            Vertical)</span>
        <div>
            <a href="habitaciones.php" class="btn-print" style="background-color: #6c757d; margin-right: 6px;">Volver al
                Mapa</a>
            <button onclick="window.print();" class="btn-print">Imprimir Planilla</button>
        </div>
    </div>

    <!-- Contenedor del documento (Hoja simulada) -->
    <div class="page-document">

        <!-- Encabezado de la planilla -->
        <div class="headers-container">
            <div class="header-meta">
                TURNO:
                <u><?php echo htmlspecialchars($_SESSION['sesion_nom_completo'] ?? $_SESSION['sesion_usuario'] ?? '_____________'); ?></u>
            </div>
            <div class="header-title">
                Control Diario
            </div>
            <div class="header-meta" style="text-align: right;">
                FECHA: <?php echo date("d/m/Y"); ?>
            </div>
        </div>

        <!-- Contenedor Superior (Control de Inventario) -->
        <div class="top-control-section" style="display: flex; justify-content: flex-start; margin-bottom: 10px;">
            <div class="control-box" style="width: auto; min-width: 260px; max-width: 340px;">
                <div class="control-box-title">Control de Inventario</div>
                <table class="table-compact">
                    <thead>
                        <tr>
                            <th class="col-prod">Producto</th>
                            <th class="text-center" style="width: 28%;">Presentación (Lt)</th>
                            <th class="col-cant">Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($prod['medida'] ?? ''); ?></td>
                                <td class="text-center" style="font-weight:bold;"><?php echo $prod['stock']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                            <td>EFECTIVO</td>
                            <td colspan="2" class="text-center">Bs. <?php echo number_format($saldo_efectivo, 2); ?>
                            </td>
                        </tr>
                        <tr style="background-color: #f8f9fa; font-weight: bold;">
                            <td>QR</td>
                            <td colspan="2" class="text-center">Bs. <?php echo number_format($saldo_qr, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Secciones de Habitaciones por Piso -->
        <?php foreach ($habitaciones_por_piso as $piso => $habs): ?>
            <div class="floor-section">
                <div class="floor-title"><?php echo $piso; ?></div>
                <div class="rooms-grid">
                    <?php foreach ($habs as $h): ?>
                        <?php
                        // Formatear pagado hasta
                        $pagado_hasta = "";
                        if (!empty($h['checkout_activo'])) {
                            $pagado_hasta = date("d/m H:i", strtotime($h['checkout_activo']));
                        }

                        // Determinar estado de campos manuales y watermark
                        $watermark = "";
                        $is_deuda = false;
                        $deuda_texto = "";
                        $strong_watermark = false;
                        $is_empty_state = in_array($h['estado'], ['LIMPIEZA', 'MANTENIMIENTO', 'MOMENTANEO', 'DISPONIBLE']);

                        switch ($h['estado']) {
                            case 'DISPONIBLE':
                                $watermark = "L";
                                $strong_watermark = true;
                                break;
                            case 'LIMPIEZA':
                                $watermark = "S";
                                $strong_watermark = true;
                                break;
                            case 'DEUDA':
                                $is_deuda = true;
                                $deuda_texto = "Debe " . $h['dias_deuda'] . " Dia" . ($h['dias_deuda'] > 1 ? "s" : "");
                                break;
                            case 'RESERVADA':
                                $watermark = "R";
                                break;
                            case 'MANTENIMIENTO':
                                $watermark = "M";
                                $strong_watermark = true;
                                break;
                            case 'OCUPADA':
                            case 'MOMENTANEO':
                            default:
                                $watermark = ""; // No se muestra watermark o la letra O en ocupada
                                break;
                        }
                        ?>
                        <div class="room-card <?php echo $is_deuda ? 'room-deuda' : ''; ?>">
                            <!-- Cabecera con número y tipo -->
                            <div class="room-card-header">
                                <?php echo $h['numero']; ?>         <?php echo $h['nombre']; ?>
                                <?php if ($is_deuda): ?>
                                    <span class="room-status-deuda-text"><?php echo $deuda_texto; ?></span>
                                <?php endif; ?>
                                <span class="btn-edit no-print" onclick="addCustomText('<?php echo $h['habitacionID']; ?>')"
                                    style="float: right; cursor: pointer; color: #007bff; font-size: 10px;">✎</span>
                            </div>

                            <!-- Cuerpo con líneas de control manual como columnas separadas verticalmente -->
                            <div class="room-card-body">
                                <div id="obs-<?php echo $h['habitacionID']; ?>"
                                    style="font-size:12px; font-weight:bold; white-space:pre-wrap; text-align:center; padding-bottom: 3px;">
                                </div>

                                <?php if ($h['estado'] === 'MANTENIMIENTO'): ?>
                                    <div
                                        style="font-size:9px; text-align:center; font-weight:bold; position:absolute; bottom:4px; left:0; width:100%; padding:0 2px; box-sizing:border-box;">
                                        MANTENIMIENTO:<br />
                                        <span
                                            style="font-weight:normal; font-style:italic;"><?php echo htmlspecialchars($h['descripcion']); ?></span>
                                    </div>
                                <?php elseif (!$is_empty_state): ?>
                                    <div class="room-data-line">
                                        <div class="room-data-line-label">hasta:</div>
                                        <div style="font-size: 12px; font-weight: normal;">
                                            <?php echo $pagado_hasta ? $pagado_hasta : ""; ?>
                                        </div>
                                        <div class="room-data-line-fill"
                                            style="border-bottom: <?php echo $pagado_hasta ? 'none' : '1px dotted #000'; ?>;"></div>
                                    </div>
                                    <div class="room-data-line">
                                        <div class="room-data-line-label">Toallas:</div>
                                        <div class="room-data-line-fill"></div>
                                    </div>
                                    <div class="room-data-line">
                                        <div class="room-data-line-label">Colchas:</div>
                                        <div class="room-data-line-fill"></div>
                                    </div>
                                    <div class="room-data-line">
                                        <div class="room-data-line-label">Control:</div>
                                        <div class="room-data-line-fill"></div>
                                    </div>
                                    <div class="room-data-line">
                                        <div class="room-data-line-label">Obs:</div>
                                        <div class="room-data-line-fill"></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Letra de Estado Central (Agua o Texto) -->
                            <?php if ($watermark && $watermark !== 'M'): ?>
                                <div class="room-status-watermark <?php echo $strong_watermark ? 'strong-watermark' : ''; ?>">
                                    <?php echo $watermark; ?>
                                </div>
                            <?php elseif ($watermark === 'M'): ?>
                                <div class="room-status-watermark strong-watermark" style="top: 40%;"><?php echo $watermark; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

    </div>

    <!-- Script de edición manual antes de imprimir -->
    <script>
        function addCustomText(habId) {
            let container = document.getElementById('obs-' + habId);
            let current = container.innerText;
            let text = prompt("Ingrese nota manuscrita para esta habitación (Se imprimirá):", current);
            if (text !== null) {
                container.innerText = text;
            }
        }
    </script>
</body>

</html>