<?php
require_once("conexion.php");
$sql = [
    "ALTER TABLE notificaciones ADD COLUMN mensaje TEXT NOT NULL AFTER empresaID;",
    "ALTER TABLE notificaciones ADD COLUMN completado TINYINT(1) NOT NULL DEFAULT 0 AFTER mensaje;",
    "ALTER TABLE notificaciones ADD COLUMN usuario_completado INT(11) NOT NULL DEFAULT 0 AFTER completado;",
    "ALTER TABLE notificaciones ADD COLUMN usuarioID INT(11) NOT NULL AFTER empresaID;"
];

foreach ($sql as $q) {
    try {
        $db->ejecutar($q);
        echo "OK: $q\n";
    } catch(Exception $e) {
        echo "Ignorado (ya existe o error): " . $e->getMessage() . "\n";
    }
}
?>
