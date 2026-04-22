<?php
session_start();
require_once 'conexion.php';  // Para conectar a la base de datos

// Habilitar el reporte de errores para depurar
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Definir la función para generar horas aleatorias
function generarNotificaciones($db) {
    // Intervalo mínimo entre notificaciones en minutos (120 minutos = 2 horas)
    $intervalo_minimo = 120;
    $hora_inicio = 10 * 60;  // 10:00 AM en minutos
    $hora_fin = 22 * 60;     // 10:00 PM en minutos

    $notificaciones = [];

    // Generar la primera hora aleatoria
    $hora1 = rand($hora_inicio, $hora_fin);
    $notificaciones[] = $hora1;

    // Generar la segunda notificación
    $hora2 = rand($hora_inicio, $hora_fin);
    // Asegurarse de que esté al menos a $intervalo_minimo minutos de la primera
    while (abs($hora2 - $hora1) < $intervalo_minimo) {
        $hora2 = rand($hora_inicio, $hora_fin);
    }
    $notificaciones[] = $hora2;

    // Generar la tercera notificación
    $hora3 = rand($hora_inicio, $hora_fin);
    // Asegurarse de que esté al menos a $intervalo_minimo minutos de la primera y segunda
    while (abs($hora3 - $hora1) < $intervalo_minimo || abs($hora3 - $hora2) < $intervalo_minimo) {
        $hora3 = rand($hora_inicio, $hora_fin);
    }
    $notificaciones[] = $hora3;

    // Ordenar las horas de menor a mayor
    sort($notificaciones);

    // Obtener la fecha y hora actuales
    $fecha_actual = new DateTime();  // Obtiene la fecha y hora actual

    // Insertar las notificaciones en la base de datos
    foreach ($notificaciones as $hora) {
        // Sumar las horas generadas al objeto DateTime
        $fecha_programada = clone $fecha_actual;  // Clonamos la fecha actual para no modificarla
        $fecha_programada->setTime(intdiv($hora, 60), $hora % 60);  // Establece la hora y los minutos

        // Convertir a formato de fecha y hora "Y-m-d H:i:s"
        $fecha_programada_str = $fecha_programada->format("Y-m-d H:i:s");

        // Depuración de la hora generada
        error_log("Generando notificación para la hora: " . $fecha_programada_str);

        $query = "INSERT INTO notificaciones (mensaje, fecha_programada, tipo)
                  VALUES ('Emitir factura', '$fecha_programada_str', 'emitir_factura')";

        try {
            // Usamos el método Execute() de ADODB para insertar la notificación
            $db->Execute($query);
            
            // Depuración para saber si la inserción fue exitosa
            error_log("Notificación creada para la hora: " . $fecha_programada_str);
        } catch (Exception $e) {
            // En caso de error, lo logueamos
            error_log("Error al insertar notificación: " . $e->getMessage());
        }
    }

    return $notificaciones;
}

// Ejecutar la función para generar las notificaciones
$notificaciones = generarNotificaciones($db);

// Responder con las notificaciones generadas
echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);
?>
