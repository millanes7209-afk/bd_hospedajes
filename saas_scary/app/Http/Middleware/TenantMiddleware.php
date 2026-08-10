<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdominio = null;

        // 1. Check if subdomain is explicitly passed via query parameter (for dev testing)
        if ($request->has('tenant')) {
            $subdominio = strtolower($request->get('tenant'));
        } else {
            // 2. Parse host subdomain (e.g. ricopollo.alloggibolivia.com or ricopollo.localhost)
            $parts = explode('.', $host);
            if (count($parts) >= 2 && $parts[0] !== 'www' && $parts[0] !== 'localhost' && !filter_var($host, FILTER_VALIDATE_IP)) {
                $subdominio = strtolower($parts[0]);
            }
        }

        // Default fallback to 'ricopollo' during initial local dev testing if no subdomain found
        if (empty($subdominio)) {
            $subdominio = 'ricopollo';
        }

        $tenant = Tenant::where('subdominio', $subdominio)->where('_estado', 'A')->first();

        if ($tenant) {
            // Store active tenant globally in Service Container
            app()->instance('tenant', $tenant);

            // Dynamically configure tenant DB connection
            config([
                'database.connections.tenant' => [
                    'driver' => 'mysql',
                    'host' => $tenant->db_host,
                    'port' => '3306',
                    'database' => $tenant->db_nombre,
                    'username' => $tenant->db_usuario,
                    'password' => $tenant->db_password ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                ],
                'database.default' => 'tenant'
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');
        }

        return $next($request);
    }
}
