<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';

    protected $fillable = [
        'nombre',
        'unidad_medida',
        'stock_actual',
        'stock_minimo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:3',
        'stock_minimo' => 'decimal:3',
    ];

    public function preciosHistorial()
    {
        return $this->hasMany(InsumoPrecioHistorial::class, 'insumo_id')
            ->orderBy('vigente_desde', 'desc')
            ->orderBy('id', 'desc');
    }

    public function precioEnFecha($fecha = null)
    {
        if (empty($fecha)) {
            return $this->ultimo_precio;
        }

        if ($this->relationLoaded('preciosHistorial')) {
            $registro = $this->preciosHistorial
                ->where('vigente_desde', '<=', $fecha)
                ->first();
        } else {
            $registro = $this->preciosHistorial()
                ->where('vigente_desde', '<=', $fecha)
                ->first();
        }

        return $registro ? (float)$registro->precio : $this->ultimo_precio;
    }

    public function getUltimoPrecioAttribute(): float
    {
        if ($this->relationLoaded('preciosHistorial')) {
            $ultimo = $this->preciosHistorial->first();
        } else {
            $ultimo = $this->preciosHistorial()->first();
        }

        return $ultimo ? (float)$ultimo->precio : 0.00;
    }

    /**
     * Calcula el costo de una cantidad determinada según la unidad usada,
     * convirtiendo automáticamente entre gramos y kilos, ml y litros, etc.
     */
    public function calcularCostoPorCantidad($cantidad, $unidadUsada = null, $fecha = null): float
    {
        $precioUnitario = $fecha ? $this->precioEnFecha($fecha) : $this->ultimo_precio;
        $cant = (float)$cantidad;
        if ($precioUnitario <= 0 || $cant <= 0) {
            return 0.00;
        }

        $baseUnidad = strtolower(trim($this->unidad_medida));
        $usada = $unidadUsada ? strtolower(trim($unidadUsada)) : $baseUnidad;

        if ($baseUnidad === $usada) {
            return (float)($cant * $precioUnitario);
        }

        // Peso: si está en kg y se usa en gramos
        if ($baseUnidad === 'kg') {
            if (in_array($usada, ['g', 'gr', 'gramo', 'gramos'])) {
                return (float)(($cant / 1000) * $precioUnitario);
            }
            if (in_array($usada, ['libra', 'lb'])) {
                return (float)(($cant * 0.5) * $precioUnitario);
            }
        } elseif (in_array($baseUnidad, ['g', 'gr', 'gramo', 'gramos'])) {
            if (in_array($usada, ['kg', 'kilo', 'kilos'])) {
                return (float)(($cant * 1000) * $precioUnitario);
            }
        }

        // Volumen: si está en litro y se usa en ml
        if (in_array($baseUnidad, ['litro', 'l'])) {
            if (in_array($usada, ['ml', 'mililitro', 'mililitros'])) {
                return (float)(($cant / 1000) * $precioUnitario);
            }
        } elseif (in_array($baseUnidad, ['ml', 'mililitro'])) {
            if (in_array($usada, ['litro', 'l'])) {
                return (float)(($cant * 1000) * $precioUnitario);
            }
        }

        // Cantidad: si está en docena y se usa en unidad
        if ($baseUnidad === 'docena') {
            if (in_array($usada, ['unidad', 'u', 'unidades'])) {
                return (float)(($cant / 12) * $precioUnitario);
            }
        } elseif (in_array($baseUnidad, ['unidad', 'u', 'unidades'])) {
            if ($usada === 'docena') {
                return (float)(($cant * 12) * $precioUnitario);
            }
        }

        return (float)($cant * $precioUnitario);
    }
}
