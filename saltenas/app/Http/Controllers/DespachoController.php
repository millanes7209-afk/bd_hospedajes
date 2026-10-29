<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\DespachoDiario;
use App\Models\DespachoDetalle;
use App\Models\VarianteSaltena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DespachoController extends Controller
{
    public function index(Request $request)
    {
        $carritoId = $request->get('carrito_id');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        $query = DespachoDiario::with(['carrito', 'detalles.variante', 'user'])
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($carritoId) {
            $query->where('carrito_id', $carritoId);
        }
        if ($fechaInicio) {
            $query->whereDate('fecha', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $query->whereDate('fecha', '<=', $fechaFin);
        }

        $despachos = $query->paginate(15)->appends($request->all());
        $carritos = Carrito::where('activo', true)->orderBy('nombre')->get();

        return view('despachos.index', compact('despachos', 'carritos', 'carritoId', 'fechaInicio', 'fechaFin'));
    }

    public function create()
    {
        $carritos = Carrito::where('activo', true)->orderBy('nombre')->get();
        $variantes = VarianteSaltena::where('activo', true)->orderBy('nombre')->get();

        return view('despachos.create', compact('carritos', 'variantes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'carrito_id' => 'required|exists:carritos,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
            'detalles' => 'required|array|min:1',
            'detalles.*.variante_id' => 'required|exists:variantes_saltena,id',
            'detalles.*.cantidad_enviada' => 'required|integer|min:0',
        ]);

        // Asegurar que la restricción unique esté eliminada de la base de datos para permitir múltiples despachos
        try {
            DB::statement("ALTER TABLE `despachos_diarios` DROP INDEX `despachos_diarios_carrito_id_fecha_unique`");
        } catch (\Throwable $e) {}

        DB::transaction(function () use ($request) {
            $despacho = DespachoDiario::create([
                'carrito_id' => $request->carrito_id,
                'fecha' => $request->fecha,
                'estado' => 'pendiente',
                'user_id' => Auth::id(),
                'observaciones' => $request->observaciones,
            ]);

            foreach ($request->detalles as $det) {
                if ((int) $det['cantidad_enviada'] > 0) {
                    DespachoDetalle::create([
                        'despacho_diario_id' => $despacho->id,
                        'variante_id' => $det['variante_id'],
                        'cantidad_enviada' => (int) $det['cantidad_enviada'],
                        'cantidad_aceptada' => 0,
                    ]);
                }
            }
        });

        return redirect()->route('despachos.index')
            ->with('success', 'Despacho registrado. El carrito podrá aceptarlo al abrir el POS.');
    }

    public function destroy($id)
    {
        $despacho = DespachoDiario::findOrFail($id);

        if ($despacho->estado === 'aceptado') {
            return back()->with('warning', 'No se puede eliminar un despacho ya aceptado por el carrito.');
        }

        $despacho->delete();

        return redirect()->route('despachos.index')->with('success', 'Despacho eliminado.');
    }
}
