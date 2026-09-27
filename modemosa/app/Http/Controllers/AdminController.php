<?php

namespace App\Http\Controllers;

use App\Models\Publicacion;
use App\Models\Reporte;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function reportes()
    {
        $reportes = Reporte::with(['usuario', 'publicacion.vendedor'])
            ->orderBy('estado')
            ->latest()
            ->paginate(15);
        return view('admin.reportes', compact('reportes'));
    }

    public function resolverReporte(Request $request, Reporte $reporte)
    {
        $request->validate(['nota_admin' => 'nullable|string|max:500']);
        $reporte->update([
            'estado' => 'revisado',
            'nota_admin' => $request->nota_admin,
        ]);
        return back()->with('success', 'Reporte marcado como revisado.');
    }

    public function eliminarPublicacion(Publicacion $publicacion)
    {
        $publicacion->update(['estado_pub' => 'expirada']);
        return back()->with('success', 'Publicación retirada del catálogo.');
    }

    public function suspenderUsuario(User $user)
    {
        $user->update(['suspendido' => true]);
        return back()->with('success', "Usuario {$user->name} suspendido.");
    }
}
