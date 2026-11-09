<?php

namespace App\Http\Middleware;

use App\Services\SchemaSyncService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUpdatedSchema
{
    public function handle(Request $request, Closure $next): Response
    {
        SchemaSyncService::ensureUpdatedColumns();

        return $next($request);
    }
}
