<?php
/**
 * FASE 2b - ULALA Migration
 * Limpiar servicios_extra (1 registro de prueba) y re-aplicar ALTER TABLE.
 */
require 'conexion.php';

// Ver qué hay en servicios_extra
echo "--- Registro existente en servicios_extra ---\n";
print_r($db->obtenerTodo("SELECT * FROM servicios_extra"));

// Borrar el registro de prueba
$db->ejecutar("DELETE FROM servicios_extra");
echo "\n✅ Registros eliminados de servicios_extra.\n";

// Ahora sí: ALTER TABLE con NOT NULL y FK
$sql = "ALTER TABLE servicios_extra
    ADD COLUMN ingresoID INT NOT NULL AFTER empresaID,
    ADD CONSTRAINT fk_servicios_ingreso
        FOREIGN KEY (ingresoID) REFERENCES ingresos(ingresoID)";

$resultado = $db->ejecutar($sql);
if ($resultado !== false) {
    echo "✅ ALTER TABLE 'servicios_extra' ejecutado correctamente.\n";
} else {
    echo "❌ Error en ALTER TABLE 'servicios_extra'.\n";
}

echo "\n--- Fase 2b completa ---\n";
