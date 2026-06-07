<?php
/**
 * Script de Migración: Estados de Habitaciones
 * Origen:  bd_dulces     -> Destino: bd_hospedajes
 * Solo sincroniza el estado de las habitaciones (Disponible, Ocupada, etc.)
 */

require_once '../../conexion.php';

// Conexión a la base de datos antigua
$db_antigua = new MiConexion("127.0.0.1", "bd_dulces", "root", "");

echo "=== INICIANDO SINCRONIZACIÓN DE ESTADOS DE HABITACIONES ===\n\n";

// 1. Obtener estados actuales de bd_dulces
$sql_select = "SELECT habitacionID, estado, _estado FROM habitaciones";
$stmt = $db_antigua->ejecutar($sql_select);

$total = 0;
$exitosos = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $total++;
    $id = (int) $row['habitacionID'];
    $estado = $row['estado'];
    $estado_auditoria = $row['_estado'];

    try {
        // Actualizar en la base de datos nueva
        $sql_update = "UPDATE habitaciones SET estado = ?, _estado = ? WHERE habitacionID = ?";
        $db->ejecutar($sql_update, [$estado, $estado_auditoria, $id]);

        $exitosos++;
        echo "  [OK] Hab ID {$id} -> Estado: {$estado}\n";
    } catch (Exception $e) {
        echo "  [ERROR] Hab ID {$id}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== PROCESO FINALIZADO ===\n";
echo "Total: {$total} | Actualizados: {$exitosos}\n\n";
