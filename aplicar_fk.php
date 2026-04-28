<?php
require_once("conexion.php");

try {
    // 1. Añadir la columna (Asumo que la PK es el primer campo, lo pondré después)
    $sql1 = "ALTER TABLE hospedajes_auditoria_montos ADD COLUMN hospedajeID INT NOT NULL AFTER _estado";
    // Nota: Uso _estado como referencia si está al inicio, o simplemente la añadiré al final si no.
    // Pero para estar seguros de no fallar por el AFTER, la añadiré normal primero.
    $sql1 = "ALTER TABLE hospedajes_auditoria_montos ADD COLUMN hospedajeID INT NOT NULL";
    
    if ($db->ejecutar($sql1) !== false) {
        echo "Columna hospedajeID creada.\n";
        
        // 2. Añadir la Foreign Key
        $sql2 = "ALTER TABLE hospedajes_auditoria_montos 
                 ADD CONSTRAINT fk_auditoria_hospedaje 
                 FOREIGN KEY (hospedajeID) REFERENCES hospedajes(hospedajeID) 
                 ON DELETE RESTRICT";
        
        if ($db->ejecutar($sql2) !== false) {
            echo "Foreign Key creada con éxito (ON DELETE RESTRICT).";
        } else {
            echo "Error al crear la Foreign Key.";
        }
    } else {
        echo "Error al crear la columna hospedajeID.";
    }

} catch (Exception $e) {
    echo "Error crítico: " . $e->getMessage();
}
?>
