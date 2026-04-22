<?php
session_start();

// Verificar que se hayan recibido los parámetros necesarios
if (isset($_GET['accion']) && isset($_GET['habitacionID']) && isset($_GET['numero']) && isset($_GET['tipo']) && isset($_GET['precio'])) {
    $accion = $_GET['accion']; // 'hospedar' o 'reservar'
    $habitacionID = $_GET['habitacionID'];
    $numero = $_GET['numero'];
    $tipo = $_GET['tipo'];
    $precio = $_GET['precio'];

    // Aquí puedes agregar más parámetros si es necesario

    // Redirigir al formulario de acuerdo a la acción
    if ($accion === 'hospedar') {
        // Redirigir al formulario de hospedaje
        header("Location: ../hospedajes/hospedaje_nuevo.php?numero=$numero&tipo=$tipo&precio=$precio&habitacionID=$habitacionID"); // Cambia la ruta según sea necesario
        exit();
    } elseif ($accion === 'reservar') {
        // Redirigir al formulario de reservas
        header("Location: ../reservas/reserva_nuevo.php?numero=$numero&tipo=$tipo&precio=$precio&habitacionID=$habitacionID"); // Cambia la ruta según sea necesario
        exit();
    } else {
        $_SESSION['message'] = 'Acción no válida.';
    }
} else {
    $_SESSION['message'] = 'Faltan datos necesarios.';
}

// Si no se realizó ninguna acción, simplemente cierra el archivo
?>
