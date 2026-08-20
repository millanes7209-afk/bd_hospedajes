<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = User::orderBy('id', 'desc')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('admin.usuarios.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|string|in:ADMINISTRADOR,CAJERO'
        ]);

        $user = User::create([
            'name' => strtoupper(trim($request->name)),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'estado' => 'A'
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.usuarios.form', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'rol' => 'required|string|in:ADMINISTRADOR,CAJERO',
            'password' => 'nullable|string|min:6'
        ]);

        $usuario->name = strtoupper(trim($request->input('name')));
        $usuario->email = strtolower(trim($request->input('email')));
        $usuario->rol = strtoupper($request->input('rol'));

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }

        $usuario->save();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::findOrFail($id);

        // Evitar que el usuario se elimine a sí mismo
        if (Session::get('usuarioID') == $usuario->id) {
            return redirect()->route('admin.usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente.');
    }

    public function profile()
    {
        $isSuperAdmin = Session::get('is_super_admin', false);
        $user = null;

        if (!$isSuperAdmin) {
            $userId = Session::get('usuarioID');
            $user = User::find($userId);
        }

        return view('admin.perfil', compact('user', 'isSuperAdmin'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        $isSuperAdmin = Session::get('is_super_admin', false);

        if ($isSuperAdmin) {
            $superAdmin = \App\Models\SuperAdmin::find(Session::get('usuarioID'));
            if ($superAdmin) {
                $superAdmin->password = Hash::make($request->input('new_password'));
                $superAdmin->save();
            }
        } else {
            $user = User::findOrFail(Session::get('usuarioID'));
            $user->password = Hash::make($request->input('new_password'));
            $user->save();
        }

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
}
