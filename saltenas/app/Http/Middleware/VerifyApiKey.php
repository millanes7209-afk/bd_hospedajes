<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Valida el token o API Key enviada por los carritos/POS remotos.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKeyConfigured = env('POS_API_KEY', 'pos_saltenas_secret_key_2026');

        $apiKeyHeader = $request->header('X-POS-Api-Key') ?? $request->bearerToken();

        if (!$apiKeyHeader || $apiKeyHeader !== $apiKeyConfigured) {
            return response()->json([
                'success' => false,
                'error' => 'Acceso Denegado: API Key no válida o ausente.',
            ], 401);
        }

        return $next($request);
    }
}
