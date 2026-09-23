<?php

namespace App\Http\Controllers;

use App\Models\CierreDiario;
use App\Models\Sucursal;
use App\Models\CostoSaltena;
use Illuminate\Http\Request;

class CierreDiarioController extends Controller
{
    public function index(Request $request)
    {
        $sucursales = Sucursal::where('activa', true)->orderBy('nombre')->get();
        $sucursal_id = $request->query('sucursal_id');

        $query = CierreDiario::with(['sucursal'])->orderBy('fecha', 'desc');

        if ($sucursal_id) {
            $query->where('sucursal_id', $sucursal_id);
        }

        $cierres = $query->paginate(15)->withQueryString();

        // Obtener costo promedio por salteña para estimaciones
        $costoPromedio = CostoSaltena::avg('costo_unidad') ?? 3.60;

        return view('cierres.index', compact('sucursales', 'cierres', 'sucursal_id', 'costoPromedio'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'fecha' => 'required|date',
            'clima' => 'required|in:frio_lluvia,templado_nublado,caluroso_soleado',
            'temp_min' => 'nullable|integer',
            'temp_max' => 'nullable|integer',
            'saltenas_vendidas' => 'required|integer|min:0',
            'saltenas_sobrantes' => 'required|integer|min:0',
            'total_efectivo' => 'required|numeric|min:0',
            'total_qr' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $totalRecaudado = floatval($request->total_efectivo) + floatval($request->total_qr);

        // Calcular costo total aproximado según insumos/variantes
        $costoPromedioUnitario = CostoSaltena::avg('costo_unidad') ?? 3.60;
        $totalProducidas = intval($request->saltenas_vendidas) + intval($request->saltenas_sobrantes);
        $costoTotalJornada = $totalProducidas * $costoPromedioUnitario;
        $gananciaNeta = $totalRecaudado - $costoTotalJornada;

        CierreDiario::updateOrCreate(
            [
                'sucursal_id' => $request->sucursal_id,
                'fecha' => $request->fecha,
            ],
            [
                'clima' => $request->clima,
                'temp_min' => $request->temp_min,
                'temp_max' => $request->temp_max,
                'saltenas_vendidas' => $request->saltenas_vendidas,
                'saltenas_sobrantes' => $request->saltenas_sobrantes,
                'total_efectivo' => $request->total_efectivo,
                'total_qr' => $request->total_qr,
                'total_recaudado' => $totalRecaudado,
                'costo_total_jornada' => $costoTotalJornada,
                'ganancia_neta' => $gananciaNeta,
                'observaciones' => $request->observaciones,
            ]
        );

        return redirect()->route('cierres.index')
            ->with('success', '¡Cierre Diario registrado exitosamente!');
    }

    public function destroy($id)
    {
        $cierre = CierreDiario::findOrFail($id);
        $cierre->delete();
        return back()->with('success', 'Registro de cierre eliminado.');
    }
}
