<?php
session_start();

// Nombre del archivo de conteo
$archivo = "contador.txt";

// Leer el valor actual del contador
if (file_exists($archivo)) {
    $contador = (int)file_get_contents($archivo);
} else {
    $contador = 0;
}

// Incrementar el contador
$contador++;

// Guardar el nuevo valor en el archivo
file_put_contents($archivo, $contador);

// Devolver el valor del contador
echo $contador;
?>
