<?php
require 'conexion.php';

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
