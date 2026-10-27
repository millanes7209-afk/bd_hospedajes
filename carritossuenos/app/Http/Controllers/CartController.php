<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\StockDiario;
use App\Services\SaltenasApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CartController extends Controller
{
    /**
     * Obtener ventas del día actual.
     */
    public function obtenerVentas()
    {
        // Si la tabla no existe (posible despliegue sin migraciones), devolvemos lista vacía
        if (! \Schema::hasTable('ventas')) {
            return response()->json(['ventas' => []]);
        }
        $hoy = now()->toDateString();
        $ventas = Venta::whereDate('fecha_hora', $hoy)
            ->with('producto:id,nombre')
            ->get()
            ->map(function ($v) {
                return [
                    'id' => $v->id,
                    'producto_id' => $v->producto_id,
                    'producto_nombre' => $v->producto->nombre ?? null,
                    'cantidad' => $v->cantidad,
                    'monto' => $v->monto,
                    'tipo_pago' => $v->tipo_pago,
                    'fecha_hora' => $v->fecha_hora,
                ];
            });
        return response()->json(['ventas' => $ventas]);
    }

    /**
     * Obtener totales por tipo de pago del día.
     */
    public function obtenerTotales()
    {
        $hoy = now()->toDateString();
        $totales = Venta::whereDate('fecha_hora', $hoy)
            ->select('tipo_pago', DB::raw('SUM(monto) as total'))
            ->groupBy('tipo_pago')
            ->pluck('total', 'tipo_pago')
            ->all();
        return response()->json([
            'total_efectivo' => $totales['efectivo'] ?? 0,
            'total_qr' => $totales['qr'] ?? 0,
        ]);
    }

    /**
     * Obtener stock diario del día actual.
     */
    public function obtenerStock()
    {
        $hoy = now()->toDateString();
        $stock = StockDiario::where('fecha', $hoy)
            ->with('producto:id,nombre')
            ->get()
            ->map(function ($s) {
                return [
                    'producto_id' => $s->producto_id,
                    'producto_nombre' => $s->producto->nombre ?? null,
                    'cantidad_enviada' => $s->cantidad_enviada,
                    'aceptado' => (bool) $s->aceptado,
                ];
            });
        return response()->json(['stock' => $stock]);
    }

    /**
     * Consultar despacho sin persistir (para la campana).
     */
    public function consultarDespacho()
    {
        $service = new SaltenasApiService();
        $hoy = now()->toDateString();
        $carritoId = env('CARROTO_ID', 1);
        $resp = $service->get('/despachos/hoy?carrito_id=' . $carritoId);
        if (! $resp || empty($resp['despacho'])) {
            return response()->json([
                'success' => true,
                'hay_despacho' => false,
                'despacho' => null,
            ]);
        }
        return response()->json([
            'success' => true,
            'hay_despacho' => true,
            'despacho' => $resp['despacho'],
        ]);
    }

    /**
     * Aceptar despacho y actualizar stock (abrir turno).
     */
    public function aceptarDespacho()
    {
        $service = new SaltenasApiService();
        $hoy = now()->toDateString();
        $carritoId = env('CARROTO_ID', 1);
        $resp = $service->get('/despachos/hoy?carrito_id=' . $carritoId);
        if (! $resp || empty($resp['despacho'])) {
            return response()->json([
                'success' => true,
                'hay_despacho' => false,
                'despacho' => null,
            ]);
        }
        $despacho = $resp['despacho'];
        foreach ($despacho['detalles'] as $det) {
            $producto = Producto::firstOrCreate(
                ['nombre' => $det['variante_nombre']],
                ['precio' => $det['precio'] ?? 0, 'activo' => true]
            );
            StockDiario::updateOrCreate(
                ['fecha' => $hoy, 'producto_id' => $producto->id],
                [
                    'cantidad_enviada' => $det['cantidad_enviada'],
                    'despacho_central_id' => $despacho['id'],
                    'aceptado' => $despacho['estado'] === 'aceptado',
                ]
            );
        }
        return response()->json([
            'success' => true,
            'hay_despacho' => true,
            'despacho' => $despacho,
        ]);
    }

    /**
     * Aceptar stock del día (marcar como aceptado).
     */
    public function aceptarStock(Request $request)
    {
        $hoy = now()->toDateString();
        DB::table('stock_diario')
            ->where('fecha', $hoy)
            ->update(['aceptado' => true]);
        return response()->json(['success' => true, 'message' => 'Stock aceptado']);
    }

    /**
     * Registrar una venta.
     */
    public function registrarVenta(Request $request)
    {
        $data = $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'promocion_id' => 'nullable|exists:promociones,id',
            'cantidad' => 'required|integer|min:1',
            'monto' => 'required|numeric|min:0',
            'tipo_pago' => 'required|in:efectivo,qr',
        ]);

        $venta = Venta::create([
            'producto_id' => $data['producto_id'],
            'promocion_id' => $data['promocion_id'] ?? null,
            'cantidad' => $data['cantidad'],
            'monto' => $data['monto'],
            'tipo_pago' => $data['tipo_pago'],
            'fecha_hora' => now(),
        ]);

        return response()->json(['success' => true, 'venta' => $venta]);
    }

    /**
     * Cerrar turno (stub implementation).
     */
    public function cerrarTurno(Request $request)
    {
        // Placeholder: implement actual turn closing logic as needed.
        return response()->json(['success' => true, 'message' => 'Turno cerrado (stub)']);
    }
}
?>