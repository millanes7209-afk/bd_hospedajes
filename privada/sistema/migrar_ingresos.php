<?php

/**
 * Script de Migración: Tabla `ingresos` + `ingreso_pagos`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_ingresos.php
 *
 * IMPORTANTE: Antes de ejecutar este script, corre en tu gestor SQL:
 * SET FOREIGN_KEY_CHECKS = 0;
 * DELETE FROM ingreso_pagos;
 * DELETE FROM ingresos;
 * ALTER TABLE ingresos AUTO_INCREMENT = 1;
 * ALTER TABLE ingreso_pagos AUTO_INCREMENT = 1;
 * SET FOREIGN_KEY_CHECKS = 1;
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$fecha_limite   = '2026-05-30 23:59:59';
$cuenta_default = 1; // Por defecto: Ingreso Hospedaje
$log_file       = __DIR__ . '/log_ambiguedades_ingresos.txt';
$hay_anomalias  = false;

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// CATÁLOGO DE FILTRADO PARA INGRESOS (EMPRESA 1)
// ─────────────────────────────────────────────
$catalogo = [
    2  => ['momentaneo', 'momentáneo'],
    3  => ['visita'],
    4  => ['baño', 'bano'],
    5  => ['ducha'],
    6  => ['recarga'],
    23 => ['alquiler', 'local'],
    1  => ['hospedaje', 'ingreso'] // Cuenta base por si dice algo genérico
];

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, int $id, string $motivo, bool &$hay_anomalias): void {
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN INGRESOS: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    $linea = "[ID Ingreso: {$id}] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// FUNCIÓN: Detectar cuentaID según tipo o descripción
// ─────────────────────────────────────────────
function detectarCuentaID(string $tipo, string $descripcion, array $catalogo, int $cuenta_default): int {
    $texto_analisis = strtolower($tipo . ' ' . $descripcion);
    
    foreach ($catalogo as $cuentaID => $palabras) {
        foreach ($palabras as $palabra) {
            if (stripos($texto_analisis, $palabra) !== false) {
                return $cuentaID;
            }
        }
    }
    return $cuenta_default;
}

// ─────────────────────────────────────────────
// EXTRACCIÓN desde bd_dulces
// ─────────────────────────────────────────────
$sql_select = "SELECT * FROM ingresos WHERE _fec_insercion <= :fecha_limite";
$stmt = $db_antigua->ejecutar($sql_select, [':fecha_limite' => $fecha_limite]);

// SQL Nueva Cabecera `ingresos` (Nota: Ya no incluye hospedajeID)
$sql_ingreso = "
    INSERT INTO ingresos (
        ingresoID, empresaID, cajaID, cuentaID, usuarioID,
        monto_total, concepto, entregado, fecha_entrega,
        recaudacionID, fecha, _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :ingresoID, :empresaID, :cajaID, :cuentaID, :usuarioID,
        :monto_total, :concepto, :entregado, :fecha_entrega,
        :recaudacionID, :fecha, :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

// SQL Nuevo Detalle `ingreso_pagos`
$sql_ingreso_pago = "
    INSERT INTO ingreso_pagos (
        ingresoID, formapagoID, monto
    ) VALUES (
        :ingresoID, :formapagoID, :monto
    )
";

$total    = 0;
$exitosos = 0;
$fallidos = 0;

echo "\n=== INICIANDO MIGRACIÓN DE INGRESOS ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['ingresoID'];

    try {
        // Determinar la cuenta contable correcta de la Empresa 1
        $cuentaID = detectarCuentaID($row['tipo'] ?? '', $row['descripcion'] ?? '', $catalogo, $cuenta_default);

        // ── INSERCIÓN 1: Cabecera ingresos ──
        $db->ejecutar($sql_ingreso, [
            ':ingresoID'         => $id,
            ':empresaID'         => 1,
            ':cajaID'            => $row['cajaID'],
            ':cuentaID'          => $cuentaID,
            ':usuarioID'         => $row['_usuario'], 
            ':monto_total'       => $row['monto'],
            ':concepto'          => trim($row['descripcion'] !== '' ? $row['descripcion'] : 'Ingreso por ' . $row['tipo']),
            ':entregado'         => 0,
            ':fecha_entrega'     => null,
            ':recaudacionID'     => null,
            ':fecha'             => $row['fecha_pago'],
            ':_fec_insercion'    => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario'          => $row['_usuario'],
            ':_estado'           => $row['_estado']
        ]);

        // ── INSERCIÓN 2: Detalle ingreso_pagos ──
        $db->ejecutar($sql_ingreso_pago, [
            ':ingresoID'   => $id,
            ':formapagoID' => $row['formaPagoID'],
            ':monto'       => $row['monto']
        ]);

        $exitosos++;
        echo "  [OK] Ingreso ID {$id} -> Cuenta {$cuentaID}\n";

    } catch (Exception $e) {
        $fallidos++;
        registrarLog($log_file, $id, "ERROR: " . $e->getMessage(), $hay_anomalias);
    }
}

echo "\n=== MIGRACIÓN DE INGRESOS FINALIZADA ===\n";
echo "Total: {$total} | Éxitos: {$exitosos} | Fallas: {$fallidos}\n\n";