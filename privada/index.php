<?php
echo "<h1>INDEX FUNCIONA</h1>";
echo "<p>Ruta: " . __DIR__ . "</p>";
echo "<p>Archivos en esta carpeta:</p>";
echo "<ul>";

$archivos = scandir(__DIR__);
foreach ($archivos as $archivo) {
    if ($archivo != '.' && $archivo != '..') {
        echo "<li>$archivo</li>";
    }
}
echo "</ul>";

// Enlace al test
echo "<p><a href='test.php'>Ir a test.php</a></p>";

// Enlace al hospedajes
echo "<p><a href='hospedajes.php'>Ir a hospedajes.php</a></p>";
?>
