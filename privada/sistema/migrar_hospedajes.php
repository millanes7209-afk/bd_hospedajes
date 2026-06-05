<?php

/**
 * Script de Migración: Tabla `hospedajes`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_hospedajes.php
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$fecha_limite   = '2026-05-30 23:59:59';
$log_file       = __DIR__ . '/log_errores_hospedajes.txt';
$hay_anomalias  = false;

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, int $id, string $motivo, bool &$hay_anomalias): void {
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN HOSPEDAJES: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    $linea = "[ID Hospedaje Antiguo: {$id}] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// EXTRACCIÓN CON INNER JOIN (Para obtener el ingresoID)
// ─────────────────────────────────────────────
$sql_select = "
    SELECT h.*, i.ingresoID 
    FROM hospedajes h
    INNER JOIN ingresos i ON i.hospedajeID = h.hospedajeID
    WHERE h._fec_insercion <= :fecha_limite
";
$stmt = $db_antigua->ejecutar($sql_select, [':fecha_limite' => $fecha_limite]);

// ─────────────────────────────────────────────
// SQL INSERCIÓN DESTINO
// ─────────────────────────────────────────────
$sql_insert = "
    INSERT INTO hospedajes (
        hospedajeID, empresaID, checkin, checkout, monto, 
        estado, habitacionID, observaciones, cajaID, ingresoID,
        _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :hospedajeID, :empresaID, :checkin, :checkout, :monto, 
        :estado, :habitacionID, :observaciones, :cajaID, :ingresoID,
        :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

$total    = 0;
$exitosos = 0;
$fallidos = 0;

echo "\n=== INICIANDO MIGRACIÓN DE HOSPEDAJES ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['hospedajeID'];

    try {
        // Mapeo y normalización de campos hacia la nueva estructura
        $db->ejecutar($sql_insert, [
            ':hospedajeID'       => $id,
            ':empresaID'         => 1, // Forzado a Empresa 1 por defecto
            ':checkin'           => $row['checkin'],
            ':checkout'          => $row['checkout'],
            ':monto'             => $row['monto_total'], // Cambio de nombre de columna: monto_total -> monto
            ':estado'            => $row['estado'] === 'ACTIVO' ? 'ACTIVO' : 'INACTIVO',
            ':habitacionID'      => $row['habitacionID'],
            ':observaciones'     => trim($row['descripcion'] ?? ''), // Mapeo de descripcion -> observaciones
            ':cajaID'            => $row['cajaID'] ?? null,
            ':ingresoID'         => $row['ingresoID'], // ¡Aquí se consolida el cruce mágico!
            ':_fec_insercion'    => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario'          => $row['_usuario'],
            ':_estado'           => $row['_estado']
        ]);

        $exitosos++;
        echo "  [OK] Hospedaje ID {$id} amarrado a Ingreso ID {$row['ingresoID']}\n";

    } catch (Exception $e) {
        $fallidos++;
        registrarLog($log_file, $id, "ERROR al insertar: " . $e->getMessage(), $hay_anomalias);
    }
}

echo "\n=== MIGRACIÓN DE HOSPEDAJES FINALIZADA ===\n";
echo "Total: {$total} | Éxitos: {$exitosos} | Fallas: {$fallidos}\n\n";