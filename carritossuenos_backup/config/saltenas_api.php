<?php

/**
 * Configuración de la API central de Salteñas.
 * carritosuenos la usa para consultar despachos y notificar aceptaciones.
 */

define('SALTENAS_API_URL', 'http://saltenas.saas-scary.com/api/v1');
define('SALTENAS_API_KEY', 'pos_saltenas_secret_key_2026');
define('SALTENAS_CARRITO_ID', 1); // ID del carrito en la tabla `carritos` de saltenas

/**
 * Hace una petición CURL a la API central de Salteñas.
 *
 * @param string $method  GET, POST, PATCH, etc.
 * @param string $endpoint  Ruta relativa, ej: '/despachos/hoy'
 * @param array  $body   Datos a enviar como JSON (solo para POST/PATCH)
 * @return array|null  Decodificado como array o null si hay error
 */
function saltenas_api(string $method, string $endpoint, array $body = []): ?array
{
    $url = SALTENAS_API_URL . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-POS-Api-Key: ' . SALTENAS_API_KEY,
    ]);

    if (!empty($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    // Permitir SSL self-signed en local
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode === 0) {
        return null; // Red no disponible
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}
