<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Usuario;
use App\Models\SuperAdmin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        $tenant = app()->bound('tenant') ? app('tenant') : null;
        $error = session('error', '');
        return view('auth.login', compact('error', 'tenant'));
    }

    public function login(Request $request)
    {
        $correoInput = trim($request->input('correo'));
        $correo = strtoupper($correoInput);
        $contrasena = $request->input('contrasena');

        // 1. Verificación en la base de datos central saas_control (Super Admin / Desarrollador)
        try {
            $superAdmin = SuperAdmin::where(function ($query) use ($correoInput) {
                $query->where('email', strtolower($correoInput))
                    ->orWhere('email', strtoupper($correoInput))
                    ->orWhere('email', $correoInput);
            })->first();

            if ($superAdmin) {
                if (Hash::check($contrasena, $superAdmin->password)) {
                    Auth::login($superAdmin);
                    Session::put('usuarioID', $superAdmin->id);
                    Session::put('nombre', $superAdmin->nombre);
                    Session::put('rolID', 'SUPER_ADMIN');
                    Session::put('is_super_admin', true);
                    Session::put('admin_logged_in', true);

                    return redirect()->route('admin.pedidos');
                } else {
                    return back()->withInput()->with('error', 'La contraseña ingresada es incorrecta.');
                }
            }
        } catch (\Throwable $e) {
            // Si la BD central saas_control no está accesible, continuar con el login del tenant sin bloquear la aplicación
            \Illuminate\Support\Facades\Log::warning('No se pudo consultar saas_control en AuthController: ' . $e->getMessage());
        }

        // 2. Verificación en la tabla 'users' de la empresa/tenant activa (usuarios del CRUD)
        $user = \App\Models\User::where(function ($query) use ($correoInput) {
            $query->where('email', strtolower($correoInput))
                ->orWhere('email', strtoupper($correoInput))
                ->orWhere('email', $correoInput);
        })->first();

        if ($user) {
            if (Hash::check($contrasena, $user->password)) {
                Auth::login($user);
                Session::put('usuarioID', $user->id);
                Session::put('nombre', $user->name);
                Session::put('rolID', strtoupper($user->rol));
                Session::put('admin_logged_in', true);

                return redirect()->route('admin.pedidos');
            } else {
                return back()->withInput()->with('error', 'La contraseña ingresada es incorrecta.');
            }
        }

        // 3. Verificación de respaldo en la tabla legacy 'usuarios' (solo si aún existe en la BD)
        if (\Illuminate\Support\Facades\Schema::hasTable('usuarios')) {
            $usuarioLegacy = \App\Models\Usuario::where('correo_electronico', $correo)
                ->orWhere('correo_electronico', strtolower($correoInput))
                ->first();

            if ($usuarioLegacy) {
                if (password_verify($contrasena, $usuarioLegacy->contrasena) || Hash::check($contrasena, $usuarioLegacy->contrasena)) {
                    Auth::login($usuarioLegacy);
                    Session::put('usuarioID', $usuarioLegacy->usuarioID);
                    Session::put('nombre', $usuarioLegacy->nombre);
                    Session::put('rolID', strtoupper($usuarioLegacy->rolID));
                    Session::put('admin_logged_in', true);

                    return redirect()->route('admin.pedidos');
                } else {
                    return back()->withInput()->with('error', 'La contraseña ingresada es incorrecta.');
                }
            }
        }

        return back()->withInput()->with('error', "El correo '$correoInput' no está registrado en el sistema.");
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('menu');
    }
}
