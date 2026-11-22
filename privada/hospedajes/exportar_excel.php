<?php
session_start();
require_once '../../conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['sesion_usuario'])) {
    exit("Acceso denegado");
}

// Parámetros capturados de la URL
$sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['empresaID'] ?? $_SESSION['sucursal_id'] ?? 1;
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Configuración de cabeceras para descargar Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Camara_Hotelera_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta individual por huésped
$sql = "SELECT 
            h.hospedajeID,
            h.checkin, 
            h.checkout,
            hab.numero AS habitacion_numero,
            c.apellido1,
            c.apellido2,
            c.nombres,
            c.ci,
            ps.nombre AS nacionalidad,
            c.fecha_nacimiento,
            c.estado_civil,
            c.profesion,
            c.lugar_nacimiento
        FROM hospedajes h
        JOIN hospedajes_clientes ch ON h.hospedajeID = ch.hospedajeID
        JOIN clientes c ON ch.clienteID = c.clienteID
        LEFT JOIN paises ps ON c.paisID = ps.paisID
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        WHERE h._estado <> 'X' AND ch._estado <> 'X' AND c._estado <> 'X'
        AND DATE(h.checkin) BETWEEN ? AND ? 
        AND h.empresaID = ?
        ORDER BY h.checkin DESC, h.hospedajeID DESC, ch.hospedajeclienteID ASC";

$rs = $db->obtenerTodo($sql, [$fecha_inicio, $fecha_fin, $sucursal_id]);

// Agrupar huéspedes por hospedaje
$hospedajes = [];
if ($rs) {
    foreach ($rs as $row) {
        $hid = $row['hospedajeID'];
        if (!isset($hospedajes[$hid])) {
            $hospedajes[$hid] = [
                'checkin'           => $row['checkin'],
                'checkout'          => $row['checkout'],
                'habitacion_numero' => $row['habitacion_numero'],
                'huespedes'         => []
            ];
        }
        $hospedajes[$hid]['huespedes'][] = $row;
    }
}

// Construcción de la tabla para Excel
echo "<table border='1'>";
echo "<thead>
        <tr style='background-color: #b5b5b5;'>
            <th>Ingreso</th>
            <th>Paterno</th>
            <th>Materno</th>
            <th>Nombre</th>
            <th>C.I.</th>
            <th>Hab</th>
            <th>Nacionalidad</th>
            <th>F. Nac.</th>
            <th>E. Civil</th>
            <th>Profesion</th>
            <th>Procedencia</th>
            <th>Salida</th>
        </tr>
      </thead>
      <tbody>";

if (!empty($hospedajes)) {
    foreach ($hospedajes as $hosp) {
        $huespedes = $hosp['huespedes'];
        $numHuespedes = count($huespedes);
        $rowspanAttr = ($numHuespedes > 1) ? " rowspan='{$numHuespedes}'" : "";

        foreach ($huespedes as $index => $huesped) {
            echo "<tr>";

            // Si es el primer huésped del hospedaje, renderizar columnas comunes con rowspan
            if ($index === 0) {
                echo "<td{$rowspanAttr} style='vertical-align: middle; text-align: center;'>" . date('d/m/Y', strtotime($hosp['checkin'])) . "</td>";
            }

            // Datos del huésped
            echo "<td>" . htmlspecialchars($huesped['apellido1'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($huesped['apellido2'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($huesped['nombres'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($huesped['ci'] ?? '') . "</td>";

            // Habitación (solo primera fila con rowspan)
            if ($index === 0) {
                echo "<td{$rowspanAttr} style='vertical-align: middle; text-align: center;'>" . htmlspecialchars($hosp['habitacion_numero'] ?? '') . "</td>";
            }

            // Datos del huésped
            $fNac = (!empty($huesped['fecha_nacimiento']) && $huesped['fecha_nacimiento'] !== '0000-00-00')
                ? date('d/m/Y', strtotime($huesped['fecha_nacimiento']))
                : '';
            echo "<td>" . htmlspecialchars($huesped['nacionalidad'] ?? '') . "</td>";
            echo "<td style='text-align: center;'>" . $fNac . "</td>";
            echo "<td>" . htmlspecialchars($huesped['estado_civil'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($huesped['profesion'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($huesped['lugar_nacimiento'] ?? '') . "</td>";

            // Salida (solo primera fila con rowspan)
            if ($index === 0) {
                $salida = (!empty($hosp['checkout']) ? date('d/m/Y', strtotime($hosp['checkout'])) : '-');
                echo "<td{$rowspanAttr} style='vertical-align: middle; text-align: center;'>" . $salida . "</td>";
            }

            echo "</tr>";
        }
    }
} else {
    echo "<tr><td colspan='12'>No hay datos en el rango seleccionado</td></tr>";
}
echo "</tbody></table>";
