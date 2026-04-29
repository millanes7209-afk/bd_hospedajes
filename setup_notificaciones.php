<?php
require_once("conexion.php");

echo "Configurando la tabla 'notificaciones'...\n";

$sql = "CREATE TABLE IF NOT EXISTS notificaciones (
    notificacionID INT(11) AUTO_INCREMENT PRIMARY KEY,
    empresaID INT(11) NOT NULL,
    usuarioID INT(11) NOT NULL COMMENT 'ID del usuario que crea la nota',
    mensaje TEXT NOT NULL,
    completado TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Pendiente, 1: Completado',
    usuario_completado INT(11) NOT NULL DEFAULT 0 COMMENT 'ID del usuario que completa la tarea. 0 si aún está pendiente',
    fecha_notificacion DATETIME NULL COMMENT 'Fecha opcional para alarmas',
    _fec_insercion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    _fec_modificacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    _usuario INT(11) NOT NULL,
    _estado CHAR(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $db->ejecutar($sql);
    echo "¡Tabla creada con éxito!\n";
    
    // Crear un índice para búsquedas rápidas de tareas pendientes por empresa
    $db->ejecutar("ALTER TABLE notificaciones ADD INDEX idx_pendientes (empresaID, completado, _estado)");
    echo "Índice de optimización añadido.\n";
} catch (Exception $e) {
    echo "Error o la tabla ya existía con índices: " . $e->getMessage() . "\n";
}
?>
