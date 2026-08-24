<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $isSuperAdmin = Session::get('is_super_admin', false);
        $userRol = Session::get('rolID', '');

        // SuperAdmin siempre tiene acceso maestro
        if ($isSuperAdmin) {
            return $next($request);
        }

        // Verificar si el rol del usuario coincide con alguno de los roles permitidos
        if (in_array(strtoupper($userRol), array_map('strtoupper', $roles))) {
            return $next($request);
        }

        return redirect()->route('admin.pedidos')->with('error', 'No tienes permisos suficientes para acceder a esta sección.');
    }
}
