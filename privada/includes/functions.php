<?php
/**
 * Funciones comunes y utilidades
 */

require_once __DIR__ . '/../config/config.php';

/**
 * Iniciar sesión de forma segura
 */
function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', 1);
        session_start();
    }
}

/**
 * Validar si el usuario está autenticado
 */
function isAuthenticated() {
    secureSessionStart();
    return isset($_SESSION['sesion_id_rol']) && !empty($_SESSION['sesion_id_rol']);
}

/**
 * Redirigir si no está autenticado
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header('Location: ../../index.php');
        exit;
    }
}

/**
 * Limpiar y sanitizar entrada de datos
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar C.I. boliviano
 */
function validateCI($ci) {
    // Eliminar espacios y caracteres no numéricos
    $ci = preg_replace('/[^0-9]/', '', $ci);
    
    // Verificar longitud (entre 7 y 8 dígitos para CI boliviano)
    if (strlen($ci) < 7 || strlen($ci) > 8) {
        return false;
    }
    
    // Verificar que todos sean dígitos
    return ctype_digit($ci);
}

/**
 * Formatear fecha
 */
function formatDate($date, $format = DATE_FORMAT) {
    $dateObj = DateTime::createFromFormat(DATE_FORMAT, $date);
    return $dateObj ? $dateObj->format($format) : $date;
}

/**
 * Calcular edad a partir de fecha de nacimiento
 */
function calculateAge($birthDate) {
    $birthDateObj = new DateTime($birthDate);
    $today = new DateTime();
    return $today->diff($birthDateObj)->y;
}

/**
 * Generar token CSRF
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validar token CSRF
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Mostrar mensaje de alerta
 */
function showAlert($message, $type = 'info') {
    $alertClass = [
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    
    $class = $alertClass[$type] ?? 'alert-info';
    
    return "<div class='alert {$class} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

/**
 * Paginar resultados
 */
function paginate($totalItems, $itemsPerPage = ITEMS_PER_PAGE, $currentPage = 1) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'total_pages' => $totalPages,
        'current_page' => $currentPage,
        'offset' => $offset,
        'has_next' => $currentPage < $totalPages,
        'has_prev' => $currentPage > 1
    ];
}

/**
 * Registrar en log
 */
function logMessage($message, $type = 'info') {
    $logFile = ERROR_LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] [{$type}] {$message}" . PHP_EOL;
    
    error_log($logEntry, 3, $logFile);
}

/**
 * Convertir texto a mayúsculas con proper case
 */
function toProperCase($string) {
    return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
}

/**
 * Validar que un string contenga solo letras y espacios
 */
function validateAlpha($string) {
    return preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $string);
}

/**
 * Validar que un string contenga solo números
 */
function validateNumeric($string) {
    return ctype_digit($string);
}

/**
 * Formatear moneda
 */
function formatCurrency($amount, $currency = 'Bs.') {
    return $currency . ' ' . number_format($amount, 2, ',', '.');
}

/**
 * Generar UUID
 */
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>
