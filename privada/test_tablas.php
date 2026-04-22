<?php
/**
 * Test simple para ver qué tablas existen
 */

echo "<h1>🔍 TEST DE TABLAS</h1>";

try {
    require_once("../../conexion.php");
    echo "<p style='color: green;'>✅ Conexión exitosa</p>";
    
    // Listar todas las tablas de la base de datos
    echo "<h2>📋 Todas las tablas en bd_hospedajes:</h2>";
    $sql = "SHOW TABLES";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($tablas as $tabla) {
        echo "<li style='color: blue;'>$tabla</li>";
    }
    echo "</ul>";
    
    // Verificar específicamente las tablas que necesitamos
    echo "<h2>🎯 Verificando tablas específicas:</h2>";
    $tablas_buscar = ['empresas', 'empleados_empresas', 'empleados', 'usuarios', 'usuarios_roles'];
    
    foreach ($tablas_buscar as $tabla) {
        if (in_array($tabla, $tablas)) {
            echo "<p style='color: green;'>✅ $tabla - EXISTE</p>";
            
            // Mostrar estructura básica
            $sql_desc = "DESCRIBE $tabla";
            $stmt_desc = $db->prepare($sql_desc);
            $stmt_desc->execute();
            $campos = $stmt_desc->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse; margin-left: 20px;'>";
            echo "<tr><th>Campo</th><th>Tipo</th></tr>";
            foreach ($campos as $campo) {
                echo "<tr><td>" . $campo['Field'] . "</td><td>" . $campo['Type'] . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<p style='color: red;'>❌ $tabla - NO EXISTE</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Archivo: " . $e->getFile() . "</p>";
    echo "<p>Línea: " . $e->getLine() . "</p>";
}
?>
