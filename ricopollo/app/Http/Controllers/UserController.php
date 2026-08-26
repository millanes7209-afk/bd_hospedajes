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
        $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
        $usuarios = User::orderBy($idCol, 'desc')->get();
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
        if (Schema::hasColumn('users', 'rolID')) {
            $userData['rolID'] = strtoupper($request->rol);
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
        $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
        $usuario = User::where($idCol, $id)->firstOrFail();
        return view('admin.usuarios.form', compact('usuario'));
    }

    public function update(Request $request, $id)
    {
        $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
        $usuario = User::where($idCol, $id)->firstOrFail();

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
        if (Schema::hasColumn('users', 'rolID')) {
            $usuario->rolID = strtoupper($request->input('rol'));
        }

        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->input('password'));
        }

        $usuario->save();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy($id)
    {
        $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
        $usuario = User::where($idCol, $id)->firstOrFail();

        if (Session::get('usuarioID') == $id || Session::get('usuario_id') == $id) {
            return redirect()->route('admin.usuarios')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return redirect()->route('admin.usuarios')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Mostrar perfil de usuario / superadmin
     */
    public function profile()
    {
        $isSuperAdmin = Session::get('is_superadmin', false);
        $userId = Session::get('usuario_id') ?? Session::get('usuarioID') ?? Session::get('user_id');

        $user = null;
        if ($userId) {
            $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
            $user = User::where($idCol, $userId)->first();
        }

        return view('admin.perfil', [
            'user' => $user,
            'isSuperAdmin' => $isSuperAdmin
        ]);
    }

    /**
     * Actualizar contraseña de perfil
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        $userId = Session::get('usuario_id') ?? Session::get('usuarioID') ?? Session::get('user_id');

        if ($userId) {
            $idCol = Schema::hasColumn('users', 'userID') ? 'userID' : 'id';
            $user = User::where($idCol, $userId)->first();
            if ($user) {
                $user->password = Hash::make($request->new_password);
                $user->save();
                return redirect()->route('admin.perfil')->with('success', 'Contraseña actualizada correctamente.');
            }
        }

        return redirect()->route('admin.perfil')->with('error', 'No se pudo actualizar la contraseña.');
    }
}
