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

        // 2. Verificación en la base de datos de la empresa/tenant activa
        $user = Usuario::where('correo_electronico', $correo)->first();

        if (!$user) {
            return back()->withInput()->with('error', "El correo '$correo' no está registrado en el sistema.");
        }

        if (!password_verify($contrasena, $user->contrasena)) {
            return back()->withInput()->with('error', 'La contraseña ingresada es incorrecta.');
        }

        Auth::login($user);
        Session::put('usuarioID', $user->usuarioID);
        Session::put('nombre', $user->nombre);
        Session::put('rolID', $user->rolID);
        Session::put('admin_logged_in', true);

        return redirect()->route('admin.pedidos');
    }

    public function logout()
    {
        Auth::logout();
        Session::flush();
        return redirect()->route('menu');
    }
}
