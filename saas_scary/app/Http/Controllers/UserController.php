<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;

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
            'email' => 'required|email',
            'password' => 'nullable|string|min:6',
            'rol' => 'required|string'
        ]);

        $nameVal = strtoupper(trim($request->name));
        $userData = [
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password ?: '123456'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('users', 'name')) {
            $userData['name'] = $nameVal;
        }
        if (Schema::hasColumn('users', 'nombre')) {
            $userData['nombre'] = $nameVal;
        }

        if (Schema::hasColumn('users', 'rol')) {
            $userData['rol'] = strtoupper($request->rol);
        }

        if (Schema::hasColumn('users', 'activo')) {
            $userData['activo'] = 1;
        }
        if (Schema::hasColumn('users', 'estado')) {
            $userData['estado'] = 'A';
        }

        User::create($userData);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit($id)
    {
        $usuario = User::where('id', $id)->firstOrFail();
        return view('admin.usuarios.form', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::where('id', $id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'rol' => 'required|string',
            'password' => 'nullable|string|min:6'
        ]);

        $nameVal = strtoupper(trim($request->input('name')));

        if (Schema::hasColumn('users', 'name')) {
            $usuario->name = $nameVal;
        }
        if (Schema::hasColumn('users', 'nombre')) {
            $usuario->nombre = $nameVal;
        }

        $usuario->email = strtolower(trim($request->input('email')));

        if (Schema::hasColumn('users', 'rol')) {
            $usuario->rol = strtoupper($request->input('rol'));
        }

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }

        $usuario->save();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $usuario = User::where('id', $id)->firstOrFail();

        if (Session::get('usuario_id') == $id) {
            return redirect()->route('admin.usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente.');
    }
}
