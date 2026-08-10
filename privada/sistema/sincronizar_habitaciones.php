<?php
/**
 * Script de Sincronización de Estados de Habitaciones
 * Origen:  bd_dulces      (Base de datos antigua)
 * Destino: bd_hospedajes  (Base de datos nueva)
 * 
 * Lógica: Sincroniza únicamente el campo 'estado' (Disponible/Ocupada/etc)
 * basándose en el habitacionID que es idéntico en ambas.
 */

// Como conexion.php ahora apunta al hosting, definiremos conexiones locales manuales
class ConexionLocal extends PDO
{
    public function __construct($database)
    {
        $dsn = "mysql:host=127.0.0.1;dbname=$database;charset=utf8";
        parent::__construct($dsn, "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function obtenerTodo($sql, $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function ejecutar($sql, $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

try {
    echo "=== SINCRONIZADOR DE ESTADOS DE HABITACIONES ===\n";

    // Conexiones manuales locales para evitar conflicto con el hosting
    $db_antigua = new ConexionLocal("bd_dulces");
    $db_nueva = new ConexionLocal("bd_hospedajes");

    echo "1. Extrayendo estados actuales de bd_dulces...\n";
    $habitaciones_antiguas = $db_antigua->obtenerTodo("SELECT habitacionID, estado, _estado FROM habitaciones");

    $total = count($habitaciones_antiguas);
    $exitosos = 0;
    $errores = 0;

    echo "2. Sincronizando {$total} habitaciones...\n";

    $sql_update = "UPDATE habitaciones SET estado = ?, _estado = ? WHERE habitacionID = ?";
    $stmt_update = $db_nueva->prepare($sql_update);

    foreach ($habitaciones_antiguas as $h) {
        try {
            $stmt_update->execute([
                $h['estado'],
                $h['_estado'],
                $h['habitacionID']
            ]);
            $exitosos++;
            echo "   [OK] Hab ID: {$h['habitacionID']} -> Estado: {$h['estado']}\n";
        } catch (Exception $e) {
            $errores++;
            echo "   [ERROR] Hab ID {$h['habitacionID']}: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== PROCESO FINALIZADO ===\n";
    echo "Total: {$total} | Éxitos: {$exitosos} | Errores: {$errores}\n";

} catch (Exception $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
}
