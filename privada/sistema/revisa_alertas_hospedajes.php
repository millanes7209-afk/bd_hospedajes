<?php

/**
 * Script de Diagnóstico: Alertas en Hospedajes y Relaciones (CMD)
 * Base de Datos: bd_hospedajes
 * Ejecutar: php revisar_alertas_hospedajes.php
 */

require_once '../../conexion.php';
// $db ya apunta a bd_hospedajes

echo "\n====================================================================\n";
echo "   REVISIÓN DE RESTRICCIONES Y ALERTAS EN HOSPEDAJES Y CLIENTES     \n";
echo "====================================================================\n";

// ────────────────────────────────────────────────────────────────────
// 1. ANALIZAR REGISTROS EN `hospedajes` CON POSIBLES FECHAS O DATOS CORRUPTOS
// ────────────────────────────────────────────────────────────────────
echo "\n🔍 [TABLA: hospedajes] Buscando filas con anomalías de datos...\n";
echo "--------------------------------------------------------------------\n";

$sql_hospedajes = "
    SELECT hospedajeID, checkin, checkout, _usuario, _estado, monto 
    FROM hospedajes 
    WHERE checkin IS NULL 
       OR checkout IS NULL 
       OR _usuario = 0 
       OR _estado = '' 
       OR _estado IS NULL
       OR checkout = '0000-00-00 00:00:00'
";
$stmt_h = $db->ejecutar($sql_hospedajes);

$cont_h = 0;
while ($row = $stmt_h->fetch(PDO::FETCH_ASSOC)) {
    $cont_h++;
    echo "-> Hospedaje ID: {$row['hospedajeID']} | Checkin: '{$row['checkin']}' | Checkout: '{$row['checkout']}' | User: {$row['_usuario']} | Estado: '{$row['_estado']}'\n";
}

if ($cont_h === 0) {
    echo " Estructura interna de hospedajes guardada sin anomalías visibles de nulidad.\n";
} else {
    echo "Total de hospedajes con datos observables: {$cont_h}\n";
}

// ────────────────────────────────────────────────────────────────────
// 2. ANALIZAR REGISTROS EN `hospedajes_clientes` CON VALORES HUÉRFANOS
// ────────────────────────────────────────────────────────────────────
echo "\n🔍 [TABLA: hospedajes_clientes] Buscando inconsistencias de auditoría...\n";
echo "--------------------------------------------------------------------\n";

$sql_hc = "
    SELECT hospedajeclienteID, hospedajeID, clienteID, _usuario, _estado 
    FROM hospedajes_clientes 
    WHERE _usuario = 0 
       OR _estado = '' 
       OR _estado IS NULL
";
$stmt_hc = $db->ejecutar($sql_hc);

$cont_hc = 0;
while ($row = $stmt_hc->fetch(PDO::FETCH_ASSOC)) {
    $cont_hc++;
    echo "-> Relación ID: {$row['hospedajeclienteID']} | HospedajeID: {$row['hospedajeID']} | ClienteID: {$row['clienteID']} | User: {$row['_usuario']} | Estado: '{$row['_estado']}'\n";
}

if ($cont_hc === 0) {
    echo " Estructura interna de hospedajes_clientes guardada de forma limpia.\n";
} else {
    echo "Total de relaciones con datos observables: {$cont_hc}\n";
}

echo "\n====================================================================\n";
echo "                  FIN DEL DIAGNÓSTICO EN CMD                        \n";
echo "====================================================================\n\n";