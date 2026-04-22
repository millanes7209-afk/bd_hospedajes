<?php
session_start();
require_once("../../conexion.php");

echo "<h2>Verificación de Tabla de Mantenimientos</h2>";

// Verificar si la tabla mantenimientos existe
try {
    $sql = "SHOW TABLES LIKE 'mantenimientos'";
    $result = $db->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "<div class='alert alert-success'>La tabla 'mantenimientos' existe.</div>";
        
        // Mostrar estructura de la tabla
        $sql_estructura = "DESCRIBE mantenimientos";
        $estructura = $db->query($sql_estructura);
        
        echo "<h3>Estructura de la tabla:</h3>";
        echo "<table class='table table-bordered'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
        
        while ($fila = $estructura->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>{$fila['Field']}</td>";
            echo "<td>{$fila['Type']}</td>";
            echo "<td>{$fila['Null']}</td>";
            echo "<td>{$fila['Key']}</td>";
            echo "<td>{$fila['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<div class='alert alert-warning'>La tabla 'mantenimientos' NO existe.</div>";
        echo "<h3>Creación de la tabla:</h3>";
        echo "<pre>
CREATE TABLE mantenimientos (
    mantenimientoID INT AUTO_INCREMENT PRIMARY KEY,
    habitacionID INT NOT NULL,
    numero VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha DATETIME NOT NULL,
    _usuario INT NOT NULL,
    _fec_insercion DATETIME NOT NULL,
    _estado VARCHAR(1) DEFAULT 'A',
    FOREIGN KEY (habitacionID) REFERENCES habitaciones(habitacionID),
    FOREIGN KEY (_usuario) REFERENCES usuarios(usuarioID)
);
        </pre>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>
