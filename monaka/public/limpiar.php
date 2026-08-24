<?php
// Limpieza directa de archivos cacheados en disco (evadiendo Laravel)
$count = 0;
// 1. Limpiar Vistas
$viewsPath = __DIR__ . '/../storage/framework/views/';
$files = glob($viewsPath . '*');
if ($files !== false) {
    foreach ($files as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            unlink($file);
            $count++;
        }
    }
}

// 2. Limpiar caché de rutas por si acaso
$routeCache1 = __DIR__ . '/../bootstrap/cache/routes.php';
$routeCache2 = __DIR__ . '/../bootstrap/cache/routes-v7.php';
if (file_exists($routeCache1))
    unlink($routeCache1);
if (file_exists($routeCache2))
    unlink($routeCache2);

echo "<h1>EXITO: Cach&eacute; de Vistas purgado</h1>";
echo "<p>Se eliminaron {$count} archivos temporales.</p>";
echo "<p><strong>Todo listo. CIERRA ESTA PESTA&Ntilde;A y refresca tu sistema con F5.</strong></p>";
?>