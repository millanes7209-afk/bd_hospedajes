<?php
/**
 * Verificar la estructura real de las tablas
 */

echo "<h1>🔍 VERIFICANDO ESTRUCTURA DE BASE DE DATOS</h1>";

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=bd_hospedajes;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>1. Tablas en bd_hospedajes:</h2>";
    $stmt = $pdo->query("SHOW TABLES");
    $tablas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tablas as $tabla) {
        echo "<p style='color: blue;'>✅ Tabla: $tabla</p>";
    }
    
    // Verificar si existe hospedajes_clientes
    if (in_array('hospedajes_clientes', $tablas)) {
        echo "<p style='color: green;'>✅ Tabla 'hospedajes_clientes' EXISTE</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabla 'hospedajes_clientes' NO EXISTE</p>";
    }
    
    echo "<h2>2. Estructura de tabla 'hospedajes':</h2>";
    $stmt = $pdo->query("DESCRIBE hospedajes");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>3. Estructura de tabla 'clientes':</h2>";
    $stmt = $pdo->query("DESCRIBE clientes");
    $columnas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    
    foreach ($columnas as $columna) {
        echo "<tr>";
        echo "<td>" . $columna['Field'] . "</td>";
        echo "<td>" . $columna['Type'] . "</td>";
        echo "<td>" . $columna['Null'] . "</td>";
        echo "<td>" . $columna['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>4. Datos de muestra:</h2>";
    
    // Verificar si hay datos en hospedajes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM hospedajes LIMIT 1");
    $total = $stmt->fetch();
    echo "<p>Total hospedajes: " . $total['total'] . "</p>";
    
    if ($total['total'] > 0) {
        // Mostrar un registro de ejemplo
        $stmt = $pdo->query("SELECT * FROM hospedajes LIMIT 1");
        $hospedaje = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Ejemplo de hospedaje:</h3>";
        echo "<pre>" . print_r($hospedaje, true) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
