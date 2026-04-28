<?php
require_once("conexion.php");

try {
    // 1. Añadir cajaID a recaudaciones (si no existe)
    $db->ejecutar("ALTER TABLE recaudaciones ADD COLUMN cajaID INT NULL AFTER empresaID");
    $db->ejecutar("ALTER TABLE recaudaciones ADD CONSTRAINT fk_recaudacion_caja FOREIGN KEY (cajaID) REFERENCES cajas(cajaID) ON DELETE RESTRICT");
    echo "CajaID añadido a recaudaciones y vinculado.\n";

    // 2. Crear FK en ingresos
    $db->ejecutar("ALTER TABLE ingresos ADD CONSTRAINT fk_ingreso_recaudacion FOREIGN KEY (recaudacionID) REFERENCES recaudaciones(recaudacionID) ON DELETE RESTRICT");
    echo "FK de recaudación añadida a ingresos.\n";

    // 3. Crear FK en egresos
    $db->ejecutar("ALTER TABLE egresos ADD CONSTRAINT fk_egreso_recaudacion FOREIGN KEY (recaudacionID) REFERENCES recaudaciones(recaudacionID) ON DELETE RESTRICT");
    echo "FK de recaudación añadida a egresos.\n";

} catch (Exception $e) {
    echo "Aviso: " . $e->getMessage() . " (Es posible que algunas ya existieran).\n";
}
?>
