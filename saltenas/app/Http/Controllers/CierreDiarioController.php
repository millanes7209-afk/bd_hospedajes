<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CierreDiario;
use App\Models\Promocion;
use App\Models\VarianteSaltena;
use App\Services\CierreValidationService;
use Illuminate\Http\Request;

class CierreDiarioController extends Controller
{
    protected $cierreService;

    public function __construct(CierreValidationService $cierreService)
    {
        $this->cierreService = $cierreService;
    }

    public function index(Request $request)
    {
        $carritoId = $request->get('carrito_id');

        $query = CierreDiario::with(['carrito', 'detalles.variante', 'detalles.promocionesDetalle.promocion']);

        if ($carritoId) {
            $query->where('carrito_id', $carritoId);
        }

        $cierres = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(15);

        $carritos = Carrito::where('activo', true)->orderBy('nombre')->get();
        $variantes = VarianteSaltena::with([
            'promociones' => function ($q) {
                $q->where('activo', true);
            }
        ])->where('activo', true)->orderBy('nombre')->get();

        return view('cierres.index', compact('cierres', 'carritos', 'variantes', 'carritoId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'carrito_id' => 'required|exists:carritos,id',
            'fecha' => 'required|date',
            'temp_min' => 'nullable|numeric',
            'temp_max' => 'nullable|numeric',
            'monto_real' => 'required|numeric|min:0',
            'detalles' => 'required|array|min:1',
            'detalles.*.variante_id' => 'required|exists:variantes_saltena,id',
            'detalles.*.cantidad_entregada' => 'required|integer|min:0',
            'detalles.*.cantidad_vendida_normal' => 'required|integer|min:0',
            'detalles.*.cantidad_sobrante' => 'required|integer|min:0',
            'observaciones' => 'nullable|string',
        ]);

        $cierre = $this->cierreService->guardarCierre($request->all());

        if ($cierre->inconsistente) {
            return redirect()->route('cierres.index')->with('warning', 'Cierre guardado. ATENCIÓN: El sistema detectó INCONSISTENCIAS en las cantidades o un descuadre en el dinero.');
        }

        return redirect()->route('cierres.index')->with('success', 'Cierre Diario registrado exitosamente. El ingreso a Bóveda Central se generó automáticamente.');
    }

    public function destroy($id)
    {
        $cierre = CierreDiario::findOrFail($id);
        $cierre->delete();

        return redirect()->route('cierres.index')->with('success', 'Registro de cierre eliminado.');
    }
}
