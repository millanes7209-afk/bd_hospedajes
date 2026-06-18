<?php
require_once("conexion.php");

// Mostrar toda info sin restricciones para diagnóstico
echo "<h2>DIAGNÓSTICO DE CAJAS</h2>";
echo "<pre>";

// 1. Ver TODAS las cajas del rango sin filtros extra
echo "=== CAJAS CON fecha_apertura ENTRE 12/06 Y 16/06 (SIN FILTROS) ===\n";
$sql = "SELECT cajaID, usuarioID, empresaID, estado, fecha_apertura, fecha_cierre 
        FROM cajas 
        WHERE fecha_apertura >= '2026-06-12 00:00:00' 
          AND fecha_apertura <= '2026-06-16 23:59:59'
        ORDER BY fecha_apertura ASC";
$rs = $db->obtenerTodo($sql);
if (empty($rs)) {
    echo ">>> SIN RESULTADOS <<<\n";
} else {
    foreach ($rs as $row) {
        echo "cajaID={$row['cajaID']} | empresaID={$row['empresaID']} | estado={$row['estado']} | apertura={$row['fecha_apertura']} | cierre={$row['fecha_cierre']}\n";
    }
}

// 2. Ver con DATE()
echo "\n=== CAJAS CON DATE(fecha_apertura) ENTRE '2026-06-08' Y '2026-06-14' ===\n";
$sql2 = "SELECT cajaID, usuarioID, empresaID, estado, fecha_apertura, fecha_cierre 
         FROM cajas 
         WHERE DATE(fecha_apertura) BETWEEN '2026-06-08' AND '2026-06-14'
         ORDER BY fecha_apertura ASC";
$rs2 = $db->obtenerTodo($sql2);
if (empty($rs2)) {
    echo ">>> SIN RESULTADOS <<<\n";
} else {
    foreach ($rs2 as $row) {
        echo "cajaID={$row['cajaID']} | estado={$row['estado']} | apertura={$row['fecha_apertura']} | cierre={$row['fecha_cierre']}\n";
    }
}

// 3. Ver cierre_cajas para esas cajas
echo "\n=== CIERRE_CAJAS VINCULADOS A CAJAS DE ESE RANGO ===\n";
$sql3 = "SELECT cc.cierrecajaID, cc.cajaID, cc.monto, cc._estado, c.fecha_apertura, c.estado as caja_estado
         FROM cierre_cajas cc
         INNER JOIN cajas c ON cc.cajaID = c.cajaID
         WHERE DATE(c.fecha_apertura) BETWEEN '2026-06-08' AND '2026-06-14'
         ORDER BY c.fecha_apertura ASC";
$rs3 = $db->obtenerTodo($sql3);
if (empty($rs3)) {
    echo ">>> SIN CIERRE_CAJAS para ese rango <<<\n";
} else {
    foreach ($rs3 as $row) {
        echo "cierrecajaID={$row['cierrecajaID']} | cajaID={$row['cajaID']} | monto={$row['monto']} | cc._estado={$row['_estado']} | caja_apertura={$row['fecha_apertura']} | caja_estado={$row['caja_estado']}\n";
    }
}

// 4. Verificar hora del servidor PHP vs MySQL
echo "\n=== HORA ACTUAL ===\n";
echo "PHP date(): " . date('Y-m-d H:i:s') . "\n";
$hora_mysql = $db->obtenerFila("SELECT NOW() as hora_mysql, @@global.time_zone as tz_global, @@session.time_zone as tz_session");
echo "MySQL NOW(): " . $hora_mysql['hora_mysql'] . "\n";
echo "MySQL TZ Global: " . $hora_mysql['tz_global'] . "\n";
echo "MySQL TZ Session: " . $hora_mysql['tz_session'] . "\n";

echo "</pre>";
?>