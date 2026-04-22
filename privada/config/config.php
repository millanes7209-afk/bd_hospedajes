<?php
/**
 * Configuración general de la aplicación
 */

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Hospedaje Dulces');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/dulces/sis_segundo_2023/');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'bd_dulces');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de sesión
define('SESSION_LIFETIME', 3600); // 1 hora
define('SESSION_PATH', '/');

// Configuración de errores
define('DEBUG_MODE', true);
define('ERROR_LOG_FILE', '../logs/error.log');

// Configuración de archivos
define('UPLOAD_PATH', '../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de fecha y hora
date_default_timezone_set('America/La_Paz');
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Configuración de paginación
define('ITEMS_PER_PAGE', 10);

// Configuración de seguridad
define('HASH_ALGORITHM', PASSWORD_DEFAULT);
define('MIN_PASSWORD_LENGTH', 8);

// Inicializar configuración
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_FILE);
}

// Configuración de cookies
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
?>
