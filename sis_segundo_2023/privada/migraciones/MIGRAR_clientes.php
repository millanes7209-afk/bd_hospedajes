<?php

/**
 * Script de Migración: Tabla `clientes`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_clientes.php
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$id_argentina = 9;
$id_bolivia = 23;
$usuario_migrador = 1;
$log_file = __DIR__ . '/log_ambiguedades_clientes.txt';

// Limpiar log anterior
file_put_contents($log_file, "=== INICIO DE MIGRACIÓN: " . date('Y-m-d H:i:s') . " ===\n");

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, int $id, string $nombre_completo, string $motivo): void
{
    $linea = "[ID Antiguo: {$id}] [{$nombre_completo}] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// FUNCIÓN: Detectar paisID según CI
// ─────────────────────────────────────────────
function detectarPaisID(string $ci, int $id_bolivia, int $id_argentina, int $id_cliente, string $nombre, string $log_file): int
{
    $ci = trim($ci);

    // CI vacío o con letras
    if (empty($ci) || !ctype_digit($ci)) {
        registrarLog($log_file, $id_cliente, $nombre, "CI anómalo ('{$ci}'): vacío o contiene letras. Se asignó Bolivia por defecto.");
        return $id_bolivia;
    }

    $longitud = strlen($ci);

    // Argentina: 8 dígitos y empieza con '9'
    if ($longitud === 8 && $ci[0] === '9') {
        return $id_argentina;
    }

    // Bolivia: 7 dígitos, O 8 dígitos que empieza con '1'
    if ($longitud === 7 || ($longitud === 8 && $ci[0] === '1')) {
        return $id_bolivia;
    }

    // Caso ambiguo
    registrarLog($log_file, $id_cliente, $nombre, "CI anómalo ('{$ci}'): no cumple reglas de Bolivia ni Argentina. Se asignó Bolivia por defecto.");
    return $id_bolivia;
}

// ─────────────────────────────────────────────
// FUNCIÓN: Validar fecha de nacimiento
// ─────────────────────────────────────────────
function validarFecha(?string $fecha, int $id_cliente, string $nombre_completo, string $log_file): string
{
    if (empty($fecha) || $fecha === '0000-00-00' || is_null($fecha)) {
        registrarLog($log_file, $id_cliente, $nombre_completo, "Fecha de nacimiento inválida ('{$fecha}'). Se asignó '1900-01-01' por defecto.");
        return '1900-01-01';
    }
    return $fecha;
}

// ─────────────────────────────────────────────
// PASO 1: LIMPIAR tabla destino
// ─────────────────────────────────────────────
echo "\n=== INICIANDO MIGRACIÓN DE CLIENTES ===\n\n";
echo "  [LIMPIEZA] Eliminando clientes existentes en bd_hospedajes...\n";

try {
    $db->ejecutar("DELETE FROM clientes", []);
    echo "  [LIMPIEZA] Tabla clientes vaciada correctamente.\n\n";
    file_put_contents($log_file, "[LIMPIEZA] Tabla clientes vaciada antes de migrar.\n", FILE_APPEND);
} catch (Exception $e) {
    $msg = "ERROR al limpiar tabla clientes: " . $e->getMessage();
    echo "  [ERROR CRÍTICO] {$msg}\n";
    file_put_contents($log_file, "[ERROR CRÍTICO] {$msg}\n", FILE_APPEND);
    exit(1);
}

// ─────────────────────────────────────────────
// PASO 2: EXTRACCIÓN desde bd_dulces
$sql_select = "SELECT * FROM clientes";
$stmt = $db_antigua->ejecutar($sql_select);

// ─────────────────────────────────────────────
// PASO 3: SQL de inserción en bd_hospedajes
// ─────────────────────────────────────────────
$sql_insert = "
    INSERT INTO clientes (
        clienteID, paisID, ci, nombres, apellido1, apellido2,
        fecha_nacimiento, lugar_nacimiento, estado_civil, profesion,
        _estado, _usuario, _fec_insercion, _fec_modificacion
    ) VALUES (
        :clienteID, :paisID, :ci, :nombres, :apellido1, :apellido2,
        :fecha_nacimiento, :lugar_nacimiento, :estado_civil, :profesion,
        :_estado, :_usuario, :_fec_insercion, :_fec_modificacion
    )
";

// ─────────────────────────────────────────────
// PASO 4: PROCESO FILA POR FILA
// ─────────────────────────────────────────────
$total = 0;
$exitosos = 0;
$fallidos = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['clienteID'];
    $nombre_completo = trim($row['nombres'] . ' ' . $row['apellidos']);

    try {
        // CI limpio
        $ci = trim($row['ci']);

        // Separar apellidos por el primer espacio
        $apellidos_raw = trim($row['apellidos']);
        $partes = explode(' ', $apellidos_raw, 2);
        $apellido1 = $partes[0] ?? null;
        $apellido2 = isset($partes[1]) && $partes[1] !== '' ? $partes[1] : null;

        // Fecha de nacimiento
        $fecha_nacimiento = validarFecha($row['fecha_nacimiento'] ?? null, $id, $nombre_completo, $log_file);

        // PaisID
        $paisID = detectarPaisID($ci, $id_bolivia, $id_argentina, $id, $nombre_completo, $log_file);

        // Insertar en bd_hospedajes
        $db->ejecutar($sql_insert, [
            ':clienteID' => $id,
            ':paisID' => $paisID,
            ':ci' => $ci,
            ':nombres' => $row['nombres'],
            ':apellido1' => $apellido1,
            ':apellido2' => $apellido2,
            ':fecha_nacimiento' => $fecha_nacimiento,
            ':lugar_nacimiento' => trim($row['lugar_nacimiento']),
            ':estado_civil' => trim($row['est_civil']),
            ':profesion' => trim($row['profesion']),
            ':_estado' => 'A',
            ':_usuario' => $usuario_migrador,
            ':_fec_insercion' => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
        ]);

        $exitosos++;
        echo "  [OK] Cliente ID {$id} - {$nombre_completo}\n";

    } catch (Exception $e) {
        $fallidos++;
        registrarLog($log_file, $id, $nombre_completo, "ERROR al insertar: " . $e->getMessage());
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
file_put_contents($log_file, $resumen, FILE_APPEND);