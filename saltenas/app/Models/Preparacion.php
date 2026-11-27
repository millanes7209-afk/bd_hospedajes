<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Preparacion extends Model
{
    use HasFactory;

    protected $table = 'preparaciones';

    protected $fillable = [
        'nombre',
        'rinde_cantidad',
        'stock_actual',
        'tipo_preparacion',
    ];

    protected $casts = [
        'rinde_cantidad' => 'decimal:2',
        'stock_actual' => 'decimal:3',
    ];

    public function producciones()
    {
        return $this->hasMany(ProduccionTanda::class, 'preparacion_id');
    }

    public function receta()
    {
        return $this->hasMany(PreparacionReceta::class, 'preparacion_id');
    }

    /**
     * Costo total del lote completo (ej. toda la tanda de masa preparada)
     */
    public function costoTotalLote($fecha = null): float
    {
        $costoTotal = 0;

        foreach ($this->receta as $item) {
            if ($item->insumo) {
                $precio = $fecha ? $item->insumo->precioEnFecha($fecha) : $item->insumo->ultimo_precio;
                $costoTotal += ((float)$item->cantidad_usada * $precio);
            }
        }

        return (float)$costoTotal;
    }

    /**
     * Costo unitario por salteña / porción (costo del lote / rinde_cantidad)
     */
    public function costoEnFecha($fecha = null): float
    {
        $rinde = (float)$this->rinde_cantidad;
        if ($rinde <= 0) {
            return 0.00;
        }

        return (float)($this->costoTotalLote($fecha) / $rinde);
    }

    /**
     * Atributo conveniente para obtener el costo unitario actual
     */
    public function getCostoUnitarioAttribute(): float
    {
        return $this->costoEnFecha();
    }

    /**
     * Desglose detallado de los insumos y costos que componen la preparación
     */
    public function desgloseCostos($fecha = null): array
    {
        $totalLote = $this->costoTotalLote($fecha);
        $rinde = (float)$this->rinde_cantidad > 0 ? (float)$this->rinde_cantidad : 1;
        $desglose = [];

        foreach ($this->receta as $item) {
            if (!$item->insumo) continue;
            $precioUnit = $fecha ? $item->insumo->precioEnFecha($fecha) : $item->insumo->ultimo_precio;
            $subtotalLote = (float)$item->cantidad_usada * $precioUnit;
            $costoPorSaltena = $subtotalLote / $rinde;
            $porcentaje = $totalLote > 0 ? ($subtotalLote / $totalLote) * 100 : 0;

            $desglose[] = [
                'insumo_id' => $item->insumo->id,
                'nombre' => $item->insumo->nombre,
                'unidad' => $item->insumo->unidad_medida,
                'cantidad_lote' => (float)$item->cantidad_usada,
                'precio_compra' => (float)$precioUnit,
                'subtotal_lote' => (float)$subtotalLote,
                'costo_por_saltena' => (float)$costoPorSaltena,
                'porcentaje_lote' => round($porcentaje, 1),
            ];
        }

        return $desglose;
    }
}
