<?php
/**
 * FASE 2 - ULALA Migration
 * ALTER TABLE: agregar ingresoID (NOT NULL) a hospedajes y servicios_extra
 * No hay datos históricos, por lo que es seguro usar NOT NULL directamente.
 */
require 'conexion.php';

$sqls = [

"ALTER TABLE hospedajes
    ADD COLUMN ingresoID INT NOT NULL AFTER cajaID,
    ADD CONSTRAINT fk_hospedajes_ingreso
        FOREIGN KEY (ingresoID) REFERENCES ingresos(ingresoID)",

"ALTER TABLE servicios_extra
    ADD COLUMN ingresoID INT NOT NULL AFTER empresaID,
    ADD CONSTRAINT fk_servicios_ingreso
        FOREIGN KEY (ingresoID) REFERENCES ingresos(ingresoID)",

];

$tablas = ['hospedajes', 'servicios_extra'];

foreach ($sqls as $i => $sql) {
    $tabla = $tablas[$i];
    $resultado = $db->ejecutar($sql);
    if ($resultado !== false) {
        echo "✅ ALTER TABLE '$tabla' ejecutado correctamente.\n";
    } else {
        echo "❌ Error en ALTER TABLE '$tabla'.\n";
    }
}

echo "\n--- Fase 2 completa ---\n";
