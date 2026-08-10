<?php
require_once('conexion.php');

$name = 'tr_ai_opcion_accesos_admin';

// SQL del trigger
$sql_trigger = "
CREATE TRIGGER $name
AFTER INSERT ON opciones
FOR EACH ROW
BEGIN
    -- Ignorar si el registro ya existe por alguna razón
    IF NOT EXISTS (SELECT 1 FROM accesos WHERE rolID = 1 AND opcionID = NEW.opcionID) THEN
        INSERT INTO accesos (rolID, opcionID, _fec_insercion, _usuario, _estado)
        VALUES (1, NEW.opcionID, NOW(), 1, 'A');
    END IF;
END;
";

try {
    // Borrar si existe antes de crear
    $db->ejecutar("DROP TRIGGER IF EXISTS $name");
    $db->ejecutar($sql_trigger);
    echo "TRIGGER_CREADO: El trigger $name ha sido instalado correctamente.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
