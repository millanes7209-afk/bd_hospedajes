<?php

/**
 * Script de Migración Unificado: `hospedajes_clientes` + `incidentes`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_clientes_e_incidentes.php
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$fecha_limite  = '2026-05-30 23:59:59';
$log_file      = __DIR__ . '/log_errores_clientes_incidentes.txt';
$hay_anomalias = false;

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, string $motivo, bool &$hay_anomalias): void {
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN UNIFICADA: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    file_put_contents($archivo, $motivo . "\n", FILE_APPEND);
    echo "  [LOG] {$motivo}\n";
}

// ====================================================================
// FASE 1: MIGRACIÓN DE `hospedajes_clientes`
// ====================================================================
echo "\n=== FASE 1: INICIANDO MIGRACIÓN DE RELACIONES HOSPEDAJES <-> CLIENTES ===\n\n";

$sql_select_hc = "SELECT * FROM hospedajes_clientes WHERE _fec_insercion <= :fecha_limite";
$stmt_hc = $db_antigua->ejecutar($sql_select_hc, [':fecha_limite' => $fecha_limite]);

$sql_insert_hc = "
    INSERT INTO hospedajes_clientes (
        hospedajeID, clienteID, empresaID,
        _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :hospedajeID, :clienteID, :empresaID,
        :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

$total_hc = 0; $ok_hc = 0; $fail_hc = 0;

while ($row = $stmt_hc->fetch(PDO::FETCH_ASSOC)) {
    $total_hc++;
    $hID = (int)$row['hospedajeID'];
    $cID = (int)$row['clienteID'];

    try {
        $db->ejecutar($sql_insert_hc, [
            ':hospedajeID'       => $hID,
            ':clienteID'         => $cID,
            ':empresaID'         => 1, // Forzado a Empresa 1
            ':_fec_insercion'    => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario'          => $row['_usuario'],
            ':_estado'           => $row['_estado']
        ]);
        $ok_hc++;
        echo "  [OK HC] Hospedaje ID {$hID} <-> Cliente ID {$cID}\n";
    } catch (Exception $e) {
        $fail_hc++;
        registrarLog($log_file, "ERROR FASE 1 (HospedajeID {$hID} / ClienteID {$cID}): " . $e->getMessage(), $hay_anomalias);
    }
}

// ====================================================================
// FASE 2: MIGRACIÓN DE `incidentes`
// ====================================================================
echo "\n=== FASE 2: INICIANDO MIGRACIÓN DE INCIDENTES HISTÓRICOS ===\n\n";

$sql_select_inc = "SELECT * FROM incidentes WHERE _fec_insercion <= :fecha_limite";
$stmt_inc = $db_antigua->ejecutar($sql_select_inc, [':fecha_limite' => $fecha_limite]);

$sql_insert_inc = "
    INSERT INTO incidentes (
        incidenteID, clienteID, empresaID, descripcion, fecha,
        estado, usuarioID, fecha_atencion, solucion,
        _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :incidenteID, :clienteID, :empresaID, :descripcion, :fecha,
        :estado, :usuarioID, :fecha_atencion, :solucion,
        :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

$total_inc = 0; $ok_inc = 0; $fail_inc = 0;

while ($row = $stmt_inc->fetch(PDO::FETCH_ASSOC)) {
    $total_inc++;
    $incID = (int)$row['incidenteID'];
    
    // Normalizar la fecha del incidente a formato Y-m-d (DATE)
    $fecha_formateada = date('Y-m-d', strtotime($row['fecha']));

    try {
        $db->ejecutar($sql_insert_inc, [
            ':incidenteID'       => $incID,
            ':clienteID'         => $row['clienteID'],
            ':empresaID'         => 1, // Forzado a Empresa 1
            ':descripcion'       => trim($row['descripcion']),
            ':fecha'             => $fecha_formateada,
            ':estado'            => 'PENDIENTE', // Valor inicial por defecto en nueva estructura
            ':usuarioID'         => $row['_usuario'], // Asignamos el usuario auditor que registró originalmente
            ':fecha_atencion'    => null,
            ':solucion'          => null,
            ':_fec_insercion'    => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario'          => $row['_usuario'],
            ':_estado'           => $row['_estado']
        ]);
        $ok_inc++;
        echo "  [OK INC] Incidente ID {$incID} registrado para Cliente ID {$row['clienteID']}\n";
    } catch (Exception $e) {
        $fail_inc++;
        registrarLog($log_file, "ERROR FASE 2 (Incidente ID {$incID}): " . $e->getMessage(), $hay_anomalias);
    }
}

// ─────────────────────────────────────────────
// RESUMEN GENERAL DE LA EJECUCIÓN
// ─────────────────────────────────────────────
echo "\n======================================================\n";
echo "===             RESUMEN DE MIGRACIÓN               ===\n";
echo "======================================================\n";
echo "Fase 1 (Hospedajes_Clientes) -> Total: {$total_hc} | Éxitos: {$ok_hc} | Fallas: {$fail_hc}\n";
echo "Fase 2 (Incidentes)          -> Total: {$total_inc} | Éxitos: {$ok_inc} | Fallas: {$fail_inc}\n";
echo "======================================================\n\n";