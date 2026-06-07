<?php

/**
 * Script de Migración: Tabla `cajas`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_cajas.php
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$log_file = __DIR__ . '/log_ambiguedades_cajas.txt';
$hay_anomalias = false;

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log (solo si hay anomalía)
// ─────────────────────────────────────────────
function registrarLog(string $archivo, int $id, string $motivo, bool &$hay_anomalias): void
{
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    $linea = "[ID Caja: {$id}] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// FUNCIÓN: Validar fecha_apertura
// ─────────────────────────────────────────────
function validarFechaApertura(?string $fecha, int $id, string $log_file, bool &$hay_anomalias): string
{
    if (empty($fecha) || $fecha === '0000-00-00 00:00:00' || is_null($fecha)) {
        $ahora = date('Y-m-d H:i:s');
        registrarLog($log_file, $id, "fecha_apertura inválida ('{$fecha}'). Se asignó la fecha actual '{$ahora}'.", $hay_anomalias);
        return $ahora;
    }
    return $fecha;
}

// ─────────────────────────────────────────────
// FUNCIÓN: Validar fecha_cierre
// ─────────────────────────────────────────────
function validarFechaCierre(?string $fecha): ?string
{
    if (empty($fecha) || $fecha === '0000-00-00 00:00:00' || is_null($fecha)) {
        return null;
    }
    return $fecha;
}

// ─────────────────────────────────────────────
// EXTRACCIÓN desde bd_dulces
$sql_select = "SELECT * FROM cajas";
$stmt = $db_antigua->ejecutar($sql_select);

// ─────────────────────────────────────────────
// SQL de inserción en bd_hospedajes
// ─────────────────────────────────────────────
$sql_insert = "
    INSERT INTO cajas (
        cajaID, _fec_insercion, _fec_modificacion, _usuario, _estado,
        empresaID, usuarioID, estado, fecha_apertura, fecha_cierre, observaciones
    ) VALUES (
        :cajaID, :_fec_insercion, :_fec_modificacion, :_usuario, :_estado,
        :empresaID, :usuarioID, :estado, :fecha_apertura, :fecha_cierre, :observaciones
    )
";

// ─────────────────────────────────────────────
// PROCESO FILA POR FILA
// ─────────────────────────────────────────────
$total = 0;
$exitosos = 0;
$fallidos = 0;

echo "\n=== INICIANDO MIGRACIÓN DE CAJAS ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['cajaID'];

    try {
        // _estado
        $estado_reg = !empty(trim($row['_estado'])) ? trim($row['_estado']) : 'A';

        // estado
        $estado = !empty(trim($row['estado'])) ? trim($row['estado']) : 'CERRADA';

        // fechas
        $fecha_apertura = validarFechaApertura($row['fecha_apertura'] ?? null, $id, $log_file, $hay_anomalias);
        $fecha_cierre = validarFechaCierre($row['fecha_cierre'] ?? null);

        $db->ejecutar($sql_insert, [
            ':cajaID' => $id,
            ':_fec_insercion' => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario' => $row['_usuario'],
            ':_estado' => $estado_reg,
            ':empresaID' => 1,
            ':usuarioID' => $row['_usuario'],
            ':estado' => $estado,
            ':fecha_apertura' => $fecha_apertura,
            ':fecha_cierre' => $fecha_cierre,
            ':observaciones' => null,
        ]);

        $exitosos++;
        echo "  [OK] Caja ID {$id}\n";

    } catch (Exception $e) {
        $fallidos++;
        registrarLog($log_file, $id, "ERROR al insertar: " . $e->getMessage(), $hay_anomalias);
    }
}

// ─────────────────────────────────────────────
// RESUMEN FINAL
// ─────────────────────────────────────────────
$resumen = "\n=== MIGRACIÓN FINALIZADA: " . date('Y-m-d H:i:s') . " ===\n"
    . "Total procesados : {$total}\n"
    . "Exitosos         : {$exitosos}\n"
    . "Fallidos         : {$fallidos}\n";

echo $resumen;

if ($hay_anomalias) {
    file_put_contents($log_file, $resumen, FILE_APPEND);
    echo "\n  [ATENCIÓN] Se encontraron anomalías. Revisa: log_ambiguedades_cajas.txt\n";
} else {
    echo "\n  [INFO] Sin anomalías. No se generó archivo de log.\n";
}