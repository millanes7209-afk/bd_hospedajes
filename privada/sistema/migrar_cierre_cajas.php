<?php

/**
 * Script de Migración: Tabla `cierre_cajas`
 * Origen:  Calculado desde ingreso_pagos + egreso_pagos (bd_hospedajes)
 * Destino: cierre_cajas (bd_hospedajes)
 * Ejecutar desde consola: php migrar_cierre_cajas.php
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$usuario_migrador = 1;
$log_file         = __DIR__ . '/log_ambiguedades_cierre_cajas.txt';
$hay_anomalias    = false;

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, string $motivo, bool &$hay_anomalias): void {
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    $linea = "[AVISO] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// PASO 1: Obtener solo cajas CERRADAS
// ─────────────────────────────────────────────
$sql_cajas = "SELECT cajaID, _usuario, _fec_insercion, _fec_modificacion, _estado 
              FROM cajas 
              WHERE estado = 'CERRADA'";
$stmt_cajas = $db->ejecutar($sql_cajas, []);

// ─────────────────────────────────────────────
// PASO 2: Query para calcular el monto por forma de pago
// (ingresos - egresos) agrupado por formapagoID
// ─────────────────────────────────────────────
$sql_movimientos = "
    SELECT formapagoID, SUM(monto) AS monto_total
    FROM (
        SELECT ip.formapagoID, ip.monto
        FROM ingreso_pagos ip
        JOIN ingresos i ON ip.ingresoID = i.ingresoID
        WHERE i.cajaID = :cajaID
          AND ip._estado <> 'X'
          AND i._estado  <> 'X'

        UNION ALL

        SELECT ep.formapagoID, (ep.monto * -1) AS monto
        FROM egreso_pagos ep
        JOIN egresos e ON ep.egresoID = e.egresoID
        WHERE e.cajaID = :cajaID2
          AND ep._estado <> 'X'
          AND e._estado  <> 'X'
    ) AS movimientos
    GROUP BY formapagoID
    HAVING SUM(monto) <> 0
";

// ─────────────────────────────────────────────
// PASO 3: SQL de inserción en cierre_cajas
// ─────────────────────────────────────────────
$sql_insert = "
    INSERT INTO cierre_cajas (
        cajaID, formapagoID, monto,
        _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :cajaID, :formapagoID, :monto,
        :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

// ─────────────────────────────────────────────
// PROCESO POR CADA CAJA CERRADA
// ─────────────────────────────────────────────
$total_cajas     = 0;
$total_registros = 0;
$fallidos        = 0;

echo "\n=== INICIANDO GENERACIÓN DE CIERRE_CAJAS ===\n\n";

while ($caja = $stmt_cajas->fetch(PDO::FETCH_ASSOC)) {
    $total_cajas++;
    $cajaID = (int) $caja['cajaID'];

    echo "  [CAJA ID {$cajaID}] Calculando movimientos...\n";

    // Calcular montos por forma de pago para esta caja
    $stmt_mov = $db->ejecutar($sql_movimientos, [
        ':cajaID'  => $cajaID,
        ':cajaID2' => $cajaID,
    ]);

    $registros_caja = 0;

    while ($mov = $stmt_mov->fetch(PDO::FETCH_ASSOC)) {
        try {
            $db->ejecutar($sql_insert, [
                ':cajaID'            => $cajaID,
                ':formapagoID'       => $mov['formapagoID'],
                ':monto'             => $mov['monto_total'],
                ':_fec_insercion'    => $caja['_fec_insercion'],
                ':_fec_modificacion' => $caja['_fec_modificacion'],
                ':_usuario'          => $caja['_usuario'],
                ':_estado'           => $caja['_estado'],
            ]);

            $total_registros++;
            $registros_caja++;
            echo "    [OK] formapagoID={$mov['formapagoID']} | monto={$mov['monto_total']}\n";

        } catch (Exception $e) {
            $fallidos++;
            registrarLog($log_file, "ERROR en cajaID={$cajaID}, formapagoID={$mov['formapagoID']}: " . $e->getMessage(), $hay_anomalias);
        }
    }

    if ($registros_caja === 0) {
        registrarLog($log_file, "CajaID={$cajaID} está CERRADA pero no tiene movimientos registrados.", $hay_anomalias);
    }
}

// ─────────────────────────────────────────────
// RESUMEN FINAL
// ─────────────────────────────────────────────
$resumen = "\n=== MIGRACIÓN FINALIZADA: " . date('Y-m-d H:i:s') . " ===\n"
         . "Cajas procesadas  : {$total_cajas}\n"
         . "Registros creados : {$total_registros}\n"
         . "Fallidos          : {$fallidos}\n";

echo $resumen;

if ($hay_anomalias) {
    file_put_contents($log_file, $resumen, FILE_APPEND);
    echo "\n  [ATENCIÓN] Se encontraron anomalías. Revisa: log_ambiguedades_cierre_cajas.txt\n";
} else {
    echo "\n  [INFO] Sin anomalías. No se generó archivo de log.\n";
}