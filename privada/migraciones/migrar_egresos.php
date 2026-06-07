<?php

/**
 * Script de Migración Corregido: Tabla `egresos` + `egreso_pagos`
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Ejecutar desde consola: php migrar_egresos.php
 *
 * IMPORTANTE: Antes de ejecutar este script, corre en tu gestor SQL:
 * SET FOREIGN_KEY_CHECKS = 0;
 * DELETE FROM egreso_pagos;
 * DELETE FROM egresos;
 * ALTER TABLE egresos AUTO_INCREMENT = 1;
 * ALTER TABLE egreso_pagos AUTO_INCREMENT = 1;
 * SET FOREIGN_KEY_CHECKS = 1;
 */

require_once '../../conexion.php';
// $db ya está instanciada y apunta a bd_hospedajes

// ─────────────────────────────────────────────
// CONFIGURACIÓN INICIAL
// ─────────────────────────────────────────────
$fecha_limite = '2040-01-01 23:59:59'; // Sin restricción real
$cuenta_default = 11; // Por seguridad, si no se reconoce, va a Gasto Personal/Turnos
$log_file = __DIR__ . '/log_ambiguedades_egresos.txt';
$hay_anomalias = false;

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

// ─────────────────────────────────────────────
// CATÁLOGO DE PALABRAS CLAVE POR CUENTA (EMPRESA 1)
// ─────────────────────────────────────────────
// El orden en este arreglo determina la prioridad de evaluación para evitar falsos positivos
$catalogo = [
    11 => [ // Gasto Personal Externo y Turnos (¡PRIORIDAD ALTA!)
        'turno',
        'turnos',
        'cancelacion',
        'cancelación',
        'reemplazo',
        'remplazo',
        'jornal',
        'sabado',
        'sábado',
        'domingo',
        'personal',
        'pagos',
        // --- Diccionario de nombres de personal externo o reemplazos ---
        'veronica',
        'verónica'
    ],
    36 => [ // Gasto Devoluciones y Reembolsos por Fallas de Servicio
        'devolucion',
        'devolución',
        'cambio',
        'cambio de habitacion',
        'cambio habitación',
        'dañado',
        'roto',
        'falla',
        'tv',
        'televisor'
    ],
    39 => [ // Gasto Aporte Cámara Hotelera y Afiliaciones
        'camara',
        'cámara',
        'hotelera',
        'aporte',
        'camara hotelera',
        'aporte mensual'
    ],
    40 => [ // Gastos por Extravíos y Reposiciones
        'extravio',
        'extravió',
        'documento',
        'reposicion',
        'reposición',
        'documento huesped',
        'huesped'
    ],
    41 => [ // Retiros Personales del Propietario
        'propietario',
        'extraccion',
        'extracción',
        'retiro',
        'dueño',
        'retiro dueño',
        'gasto propietario'
    ],
    8 => [ // Gasto Insumos de Limpieza
        'ambientador',
        'suavizante',
        'lavandina',
        'bolsas',
        'bolsa',
        'detergente',
        'ace',
        'jaboncillo',
        'papel',
        'limpieza',
        'balde',
        'trapos',
        'trapo',
        'productos de limpieza',
        'insumos',
        'escoba',
        'trapeador',
        'desinfectante',
        'cloro',
        'lejia',
        'frazadas',
        'frazada',
        'habitaciones limpieza' // Incorporados aquí
    ],
    9 => [ // Gasto Mantenimiento, Materiales y Equipos (¡FUSIONADOS!)
        'pintura',
        'tuko',
        'masilla',
        'espatula',
        'rodillo',
        'rodillos',
        'macilla',
        'chapas',
        'chapa',
        'arreglo',
        'reparacion',
        'mantenimiento',
        'pintar',
        'pared',
        'cemento',
        'gasfiter',
        'electricidad',
        'electricista',
        'plomero',
        'plomeria',
        'llave',
        'instalacion',
        // --- Ex palabras de la cuenta 504 unificadas aquí ---
        'materiales',
        'herramientas',
        'equipos',
        'foco',
        'focos',
        'cable',
        'cables',
        'enchufe',
        'ducha',
        'accesorio',
        'accesorios',
        'ferreteria'
    ],
    7 => [ // Gasto Refrigerio
        'almuerzo',
        'chicas',
        'refresco',
        'desayuno',
        'agua',
        'parrilla',
        'refrigerio',
        'comida',
        'cena',
        'merienda',
        'snack',
        'cafe',
        'café'
    ]
];

// ─────────────────────────────────────────────
// FUNCIÓN: Registrar en el log
// ─────────────────────────────────────────────
function registrarLog(string $archivo, int $id, string $motivo, bool &$hay_anomalias): void
{
    if (!$hay_anomalias) {
        file_put_contents($archivo, "=== INICIO DE MIGRACIÓN: " . date('Y-m-d H:i:s') . " ===\n");
        $hay_anomalias = true;
    }
    $linea = "[ID Egreso: {$id}] - {$motivo}\n";
    file_put_contents($archivo, $linea, FILE_APPEND);
    echo "  [LOG] {$linea}";
}

// ─────────────────────────────────────────────
// FUNCIÓN: Detectar cuentaID según descripción
// ─────────────────────────────────────────────
function detectarCuentaID(string $descripcion, int $id, array $catalogo, int $cuenta_default, string $log_file, bool &$hay_anomalias): int
{
    $desc = strtolower(trim($descripcion));

    if ($desc === '') {
        registrarLog($log_file, $id, "Descripción vacía. Se asignó cuentaID={$cuenta_default} por defecto.", $hay_anomalias);
        return $cuenta_default;
    }

    // Recorre el catálogo respetando el orden estricto de prioridades establecido
    foreach ($catalogo as $cuentaID => $palabras) {
        foreach ($palabras as $palabra) {
            if (stripos($desc, $palabra) !== false) {
                return $cuentaID;
            }
        }
    }

    // Si tiene texto pero no se encontró coincidencia segura en ningún filtro
    registrarLog($log_file, $id, "No clasificado de forma segura: '{$descripcion}'. Forzado a cuentaID={$cuenta_default}.", $hay_anomalias);
    return $cuenta_default;
}

// ─────────────────────────────────────────────
// EXTRACCIÓN desde bd_dulces
// ─────────────────────────────────────────────
$sql_select = "SELECT * FROM egresos";
$stmt = $db_antigua->ejecutar($sql_select);

// ─────────────────────────────────────────────
// SQL inserción cabecera: egresos
// ─────────────────────────────────────────────
$sql_egreso = "
    INSERT INTO egresos (
        egresoID, empresaID, cajaID, cuentaID, usuarioID,
        monto_total, concepto, fecha, entregado, fecha_entrega,
        recaudacionID, _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :egresoID, :empresaID, :cajaID, :cuentaID, :usuarioID,
        :monto_total, :concepto, :fecha, :entregado, :fecha_entrega,
        :recaudacionID, :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

// ─────────────────────────────────────────────
// SQL inserción detalle: egreso_pagos
// ─────────────────────────────────────────────
$sql_egreso_pago = "
    INSERT INTO egreso_pagos (
        egresoID, formapagoID, monto,
        _fec_insercion, _fec_modificacion, _usuario, _estado
    ) VALUES (
        :egresoID, :formapagoID, :monto,
        :_fec_insercion, :_fec_modificacion, :_usuario, :_estado
    )
";

// ─────────────────────────────────────────────
// PROCESO FILA POR FILA
// ─────────────────────────────────────────────
$total = 0;
$exitosos = 0;
$fallidos = 0;

echo "\n=== INICIANDO MIGRACIÓN DE EGRESOS OPTIMIZADA ===\n\n";

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['egresoID'];

    try {
        $cuentaID = detectarCuentaID(
            $row['descripcion'] ?? '',
            $id,
            $catalogo,
            $cuenta_default,
            $log_file,
            $hay_anomalias
        );

        // ── INSERCIÓN 1: cabecera egresos (Multiempresa forzado a 1) ──
        $db->ejecutar($sql_egreso, [
            ':egresoID' => $id,
            ':empresaID' => 1,
            ':cajaID' => $row['cajaID'],
            ':cuentaID' => $cuentaID,
            ':usuarioID' => $row['_usuario'],
            ':monto_total' => $row['monto'],
            ':concepto' => trim($row['descripcion']),
            ':fecha' => $row['fecha_pago'],
            ':entregado' => 0,
            ':fecha_entrega' => null,
            ':recaudacionID' => null,
            ':_fec_insercion' => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario' => $row['_usuario'],
            ':_estado' => $row['_estado'],
        ]);

        // ── INSERCIÓN 2: detalle egreso_pagos ──
        $db->ejecutar($sql_egreso_pago, [
            ':egresoID' => $id,
            ':formapagoID' => $row['formaPagoID'],
            ':monto' => $row['monto'],
            ':_fec_insercion' => $row['_fec_insercion'],
            ':_fec_modificacion' => $row['_fec_modificacion'],
            ':_usuario' => $row['_usuario'],
            ':_estado' => $row['_estado'],
        ]);

        $exitosos++;
        echo "  [OK] Egreso ID {$id} -> Cuenta {$cuentaID} - " . trim($row['descripcion']) . "\n";

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
    echo "\n  [ATENCIÓN] Se generaron alertas por ambigüedad. Revisa: log_ambiguedades_egresos.txt\n";
} else {
    echo "\n  [INFO] Proceso limpio de ambigüedades. No se requirió archivo de log.\n";
}