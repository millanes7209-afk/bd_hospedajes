<?php
/**
 * Utilidades compartidas para módulo de hospedajes
 */

// Función para limpiar los datos concatenados (reemplaza el pipe por coma para legibilidad)
function formatearLista($texto) {
    return $texto ? str_replace('|', ', ', $texto) : '-';
}

// Función para obtener nombres de formas de pago desde IDs concatenados
function obtenerFormasPago($formapagoIDs) {
    global $db;
    
    if (!$formapagoIDs) {
        return '-';
    }
    
    $ids = explode(',', $formapagoIDs);
    $ids = array_filter($ids, function($id) {
        return !empty($id) && is_numeric($id);
    });
    
    if (empty($ids)) {
        return '-';
    }
    
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $sql = "SELECT tipo FROM formas_pago WHERE formaPagoID IN ($placeholders) AND _estado = 'A'";
    
    $formas_pago = $db->obtenerTodo($sql, $ids);
    
    if (!$formas_pago) {
        return '-';
    }
    
    $nombres = array_column($formas_pago, 'tipo');
    return implode(', ', $nombres);
}

// Función para calcular las edades de los clientes en la habitación
function calcularEdad($fechas_nacimiento, $separador = ', ') {
    if (!$fechas_nacimiento) return '-';
    $fechas = explode('|', $fechas_nacimiento);
    $edades = [];
    $hoy = new DateTime();
    foreach ($fechas as $f) {
        if ($f && $f !== '0000-00-00') {
            $fnac = new DateTime($f);
            $edades[] = $hoy->diff($fnac)->y;
        }
    }
    return empty($edades) ? '-' : implode($separador, $edades);
}

// Función para verificar sesión
function verificarSesion() {
    if (!isset($_SESSION['sesion_usuario'])) {
        header("Location: ../../index.php");
        exit();
    }
}

// Función para obtener nombre de empresa
function obtenerNombreEmpresa() {
    return $_SESSION['empresa_nombre'] ?? $_SESSION['nombre_empresa'] ?? 'DULCES SUEÑOS';
}

// Función para generar encabezado de impresión
function generarEncabezadoImpresion($titulo, $fecha_inicio = null, $fecha_fin = null) {
    $empresa = obtenerNombreEmpresa();
    $html = '<div class="text-center mb-4 print-header">';
    $html .= '<h4><strong>' . htmlspecialchars($titulo) . '</strong></h4>';
    $html .= '<p class="mb-1"><strong>Empresa:</strong> ' . htmlspecialchars($empresa) . '</p>';
    
    if ($fecha_inicio && $fecha_fin) {
        $html .= '<p class="mb-1"><strong>Del:</strong> ' . date('d/m/Y', strtotime($fecha_inicio)) . ' al ' . date('d/m/Y', strtotime($fecha_fin)) . '</p>';
    }
    
    $html .= '<p class="mb-0"><strong>Fecha de impresión:</strong> ' . date('d/m/Y H:i') . '</p>';
    $html .= '</div>';
    
    return $html;
}
?>
