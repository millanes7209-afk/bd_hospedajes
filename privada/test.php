<?php
echo "<h1>¡FUNCIONA!</h1>";
echo "<p>Estás en: " . __DIR__ . "</p>";
echo "<p>URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Servidor: " . $_SERVER['SERVER_NAME'] . "</p>";
phpinfo();
?>
