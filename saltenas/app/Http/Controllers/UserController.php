<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        $currentUserId = Auth::id();

        return view('usuarios.index', compact('users', 'currentUserId'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:4',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Tu contraseña ha sido actualizada correctamente.');
    }
}
