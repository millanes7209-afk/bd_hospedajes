<?php
require_once("conexion.php");

try {
    // 1. Actualizar Vista v_movimientos_caja (Snapshot Dinámico)
    $sqlView = "CREATE OR REPLACE VIEW v_movimientos_caja AS " . trim($db->getVistaMovimientos(), "()");

    $db->ejecutar($sqlView);
    echo "✅ Vista v_movimientos_caja actualizada desde PHP.\n";

    // 2. Crear Índices de Rendimiento
    // (Usamos try-catch interno porque ALTER TABLE no soporta IF NOT EXISTS en todos los motores antiguos)
    try {
        $db->ejecutar("ALTER TABLE ingresos ADD INDEX idx_caja_empresa (cajaID, empresaID)");
    } catch (Exception $e) {
    }
    try {
        $db->ejecutar("ALTER TABLE egresos ADD INDEX idx_caja_empresa (cajaID, empresaID)");
    } catch (Exception $e) {
    }
    try {
        $db->ejecutar("ALTER TABLE ingresos ADD INDEX idx_fecha (fecha)");
    } catch (Exception $e) {
    }
    try {
        $db->ejecutar("ALTER TABLE egresos ADD INDEX idx_fecha (fecha)");
    } catch (Exception $e) {
    }

    echo "✅ Índices de rendimiento creados (o ya existían).";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>