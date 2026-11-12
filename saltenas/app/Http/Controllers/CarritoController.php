<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoTurnoVivo;
use App\Services\SchemaSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarritoController extends Controller
{
    public function index()
    {
        SchemaSyncService::ensureUpdatedColumns();

        $carritos = Carrito::orderBy('nombre')->get();
        $turnosVivos = $this->obtenerTurnosAbiertosFormateados();

        return view('carritos.index', compact('carritos', 'turnosVivos'));
    }

    public function estadoVivo(): JsonResponse
    {
        SchemaSyncService::ensureUpdatedColumns();

        return response()->json([
            'success' => true,
            'turnos_vivos' => $this->obtenerTurnosAbiertosFormateados(),
            'actualizado_servidor' => now()->timezone('America/La_Paz')->format('H:i:s'),
        ]);
    }

    protected function obtenerTurnosAbiertosFormateados(): array
    {
        $registros = CarritoTurnoVivo::with('carrito')
            ->where('estado', 'abierto')
            ->whereHas('carrito', fn($q) => $q->where('activo', true))
            ->orderByDesc('ultima_actualizacion_pos')
            ->get();

        return $registros->map(function (CarritoTurnoVivo $tv) {
            $ultimaPos = $tv->ultima_actualizacion_pos ?? $tv->updated_at ?? now();
            $segundosDesdeSenal = max(0, (int) $ultimaPos->diffInSeconds(now()));

            if ($segundosDesdeSenal < 60) {
                $haceTexto = 'hace ' . $segundosDesdeSenal . ' seg';
            } else {
                $mins = (int) floor($segundosDesdeSenal / 60);
                $haceTexto = 'hace ' . $mins . ' min';
            }

            return [
                'carrito_id' => (int) $tv->carrito_id,
                'carrito_nombre' => strtoupper($tv->carrito->nombre ?? ('Carrito #' . $tv->carrito_id)),
                'carrito_zona' => strtoupper($tv->carrito->zona ?: 'SIN ZONA'),
                'turno_remoto_id' => $tv->turno_remoto_id ? (int) $tv->turno_remoto_id : null,
                'stock_aceptado' => (bool) $tv->stock_aceptado,
                'abierto_hora' => $tv->abierto_at
                    ? $tv->abierto_at->copy()->timezone('America/La_Paz')->format('H:i')
                    : '—',
                'hace_texto' => $haceTexto,
                'segundos_sin_senal' => $segundosDesdeSenal,
                'total_enviadas' => (int) $tv->total_enviadas,
                'total_vendidas' => (int) $tv->total_vendidas,
                'total_restantes' => (int) $tv->total_restantes,
                'dinero_total' => (float) $tv->dinero_total,
                'dinero_efectivo' => (float) $tv->dinero_efectivo,
                'dinero_qr' => (float) $tv->dinero_qr,
                'desglose_variedades' => is_array($tv->desglose_variedades) ? $tv->desglose_variedades : [],
            ];
        })->values()->all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'zona' => 'nullable|string|max:255',
        ]);

        Carrito::create([
            'nombre' => $request->nombre,
            'zona' => $request->zona,
            'activo' => true,
        ]);

        return redirect()->route('carritos.index')->with('success', 'Carrito / Punto de venta registrado.');
    }

    public function toggleEstado($id)
    {
        $carrito = Carrito::findOrFail($id);
        $carrito->activo = !$carrito->activo;
        $carrito->save();

        return redirect()->route('carritos.index')->with('success', 'Estado del carrito actualizado.');
    }
}
