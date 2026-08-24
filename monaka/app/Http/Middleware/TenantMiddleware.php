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

        // Si el subdominio es 'scary', estamos en el entorno del SuperAdmin Central
        if ($subdominio === 'scary') {
            app()->instance('is_super_admin_panel', true);
            self::setupSaasControlConnection(true);
            return $next($request);
        }

        // Asegurar que saas_control esté configurada en segundo plano para consultas de SuperAdmin
        self::setupSaasControlConnection(false);

        // Resolver Tenant sobre la base de datos por defecto o tenant
        try {
            $tenant = Tenant::where('subdominio', $subdominio)->where('_estado', 'A')->first();
        } catch (\Throwable $e) {
            $tenant = null;
        }

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

    /**
     * Configuración tolerante a fallos para la base de datos central saas_control
     */
    public static function setupSaasControlConnection(bool $setAsDefault = false)
    {
        $host = env('DB_CONTROL_HOST', 'sdb-90.hosting.stackcp.net');
        $db = env('DB_CONTROL_DATABASE', 'saas_control-35313139e726');
        $user = env('DB_CONTROL_USERNAME', 'saas_control-35313139e726');
        $passwords = array_filter([
            env('DB_CONTROL_PASSWORD', 'NuevaNueva'),
            'SCARYmovie1.',
            'NuevaNueva',
            env('DB_PASSWORD', '')
        ]);

        foreach ($passwords as $pass) {
            try {
                config([
                    'database.connections.saas_control' => [
                        'driver' => 'mysql',
                        'host' => $host,
                        'port' => '3306',
                        'database' => $db,
                        'username' => $user,
                        'password' => $pass,
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => false,
                    ]
                ]);

                if ($setAsDefault) {
                    config(['database.default' => 'saas_control']);
                }

                DB::purge('saas_control');
                DB::reconnect('saas_control');
                DB::connection('saas_control')->getPdo();
                return; // Conexión exitosa
            } catch (\Throwable $e) {
                continue;
            }
        }

        if ($setAsDefault) {
            config(['database.default' => env('DB_CONNECTION', 'mysql')]);
        }
    }
}
