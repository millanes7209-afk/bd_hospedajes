<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('admin_logged_in') && !Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder al panel de administración.');
        }

        return $next($request);
    }
}
