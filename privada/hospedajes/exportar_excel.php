<?php
session_start();
require_once '../../conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['sesion_usuario'])) {
    exit("Acceso denegado");
}

// Parámetros capturados de la URL
$sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? 1;
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

// Configuración de cabeceras para descargar Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Camara_Hotelera_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta con TODOS los campos de la tabla original
$sql = "SELECT 
            h.checkin, h.checkout,
            GROUP_CONCAT(c.apellido1 SEPARATOR ' | ') as paternos,
            GROUP_CONCAT(c.apellido2 SEPARATOR ' | ') as maternos,
            GROUP_CONCAT(c.nombres SEPARATOR ' | ') as nombres_ind,
            GROUP_CONCAT(c.ci SEPARATOR ', ') as cis,
            GROUP_CONCAT(c.pais SEPARATOR ' | ') as nacionalidades,
            GROUP_CONCAT(c.fecha_nacimiento SEPARATOR ' | ') as fechas_nac,
            GROUP_CONCAT(c.estado_civil SEPARATOR ' | ') as estados_civiles,
            GROUP_CONCAT(c.profesion SEPARATOR ' | ') as profesiones,
            GROUP_CONCAT(c.lugar_nacimiento SEPARATOR ' | ') as procedencias,
            hab.numero AS habitacion_numero
        FROM hospedajes h
        JOIN hospedajes_clientes ch ON h.hospedajeID = ch.hospedajeID
        JOIN clientes c ON ch.clienteID = c.clienteID
        JOIN habitaciones hab ON h.habitacionID = hab.habitacionID
        WHERE h._estado <> 'X' AND ch._estado <> 'X' AND c._estado <> 'X'
        AND DATE(h.checkin) BETWEEN ? AND ? 
        AND h.empresaID = ?
        GROUP BY h.hospedajeID
        ORDER BY h.checkin DESC";

$rs = $db->obtenerTodo($sql, [$fecha_inicio, $fecha_fin, $sucursal_id]);

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

if ($rs) {
    foreach ($rs as $fila) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($fila['checkin'])) . "</td>";
        echo "<td>" . htmlspecialchars($fila['paternos']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['maternos']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['nombres_ind']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['cis']) . "</td>";
        echo "<td style='text-align: center;'>" . $fila['habitacion_numero'] . "</td>";
        echo "<td>" . htmlspecialchars($fila['nacionalidades']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['fechas_nac']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['estados_civiles']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['profesiones']) . "</td>";
        echo "<td>" . htmlspecialchars($fila['procedencias']) . "</td>";
        echo "<td>" . ($fila['checkout'] ? date('d/m/Y', strtotime($fila['checkout'])) : '-') . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='12'>No hay datos en el rango seleccionado</td></tr>";
}
echo "</tbody></table>";