<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeDecimalInputs
{
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $trimmed = trim($value);
                // Si contiene comas como separadores decimales (ej. 3,5 o 12,50)
                if (preg_match('/^-?\d+,\d+$/', $trimmed)) {
                    $value = str_replace(',', '.', $trimmed);
                }
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
