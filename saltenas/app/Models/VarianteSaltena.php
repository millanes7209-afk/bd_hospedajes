<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VarianteSaltena extends Model
{
    use HasFactory;

    protected $table = 'variantes_saltena';

    protected $fillable = [
        'nombre',
        'precio_venta',
        'activo',
    ];

    protected $casts = [
        'precio_venta' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function recetas()
    {
        return $this->hasMany(VarianteReceta::class, 'variante_id');
    }

    public function promociones()
    {
        return $this->hasMany(Promocion::class, 'variante_id');
    }

    /**
     * Costo unitario dinámico total de producir 1 salteña de esta variante
     * Suma los insumos directos y las preparaciones (ej. masa) con el último precio de compra.
     */
    public function costoEnFecha($fecha = null): float
    {
        $costoTotalUnitario = 0;

        foreach ($this->recetas as $receta) {
            $cantidad = (float)$receta->cantidad_usada;
            if ($cantidad <= 0) continue;

            if ($receta->tipo_componente === 'insumo' && $receta->insumo) {
                $precioInsumo = $fecha ? $receta->insumo->precioEnFecha($fecha) : $receta->insumo->ultimo_precio;
                $costoTotalUnitario += ($cantidad * $precioInsumo);
            } elseif ($receta->tipo_componente === 'preparacion' && $receta->preparacion) {
                $costoPrepUnidad = $receta->preparacion->costoEnFecha($fecha);
                $costoTotalUnitario += ($cantidad * $costoPrepUnidad);
            }
        }

        return (float)$costoTotalUnitario;
    }

    public function getCostoProduccionAttribute(): float
    {
        return $this->costoEnFecha();
    }

    public function margenBruto($fecha = null): float
    {
        return (float)($this->precio_venta - $this->costoEnFecha($fecha));
    }

    public function margenPorcentaje($fecha = null): float
    {
        $pv = (float)$this->precio_venta;
        if ($pv <= 0) {
            return 0.00;
        }

        return round(($this->margenBruto($fecha) / $pv) * 100, 1);
    }

    /**
     * Estado del margen para semáforo visual:
     * - 'sin_receta': no tiene ingredientes asignados
     * - 'excelente': margen >= 50%
     * - 'aceptable': margen >= 30% y < 50%
     * - 'critico': margen < 30% o pérdida
     */
    public function estadoMargen($fecha = null): string
    {
        $costo = $this->costoEnFecha($fecha);
        $pv = (float)$this->precio_venta;

        if ($costo <= 0) {
            return 'sin_receta';
        }

        if ($pv <= 0 || $costo >= $pv) {
            return 'critico';
        }

        $margen = $this->margenPorcentaje($fecha);
        if ($margen >= 50) {
            return 'excelente';
        }
        if ($margen >= 30) {
            return 'aceptable';
        }

        return 'critico';
    }

    /**
     * Desglose completo de cada componente (masa, carnes, aderezos) que aporta al costo de 1 salteña
     */
    public function desgloseCostos($fecha = null): array
    {
        $costoTotal = $this->costoEnFecha($fecha);
        $desglose = [];

        foreach ($this->recetas as $receta) {
            $cant = (float)$receta->cantidad_usada;
            if ($cant <= 0) continue;

            if ($receta->tipo_componente === 'insumo' && $receta->insumo) {
                $precio = $fecha ? $receta->insumo->precioEnFecha($fecha) : $receta->insumo->ultimo_precio;
                $subtotal = $cant * $precio;
                $porcentaje = $costoTotal > 0 ? ($subtotal / $costoTotal) * 100 : 0;

                $desglose[] = [
                    'tipo' => 'insumo',
                    'id' => $receta->insumo->id,
                    'nombre' => $receta->insumo->nombre,
                    'unidad' => $receta->insumo->unidad_medida,
                    'cantidad' => $cant,
                    'precio_unitario' => (float)$precio,
                    'costo_aporte' => (float)$subtotal,
                    'porcentaje' => round($porcentaje, 1),
                ];
            } elseif ($receta->tipo_componente === 'preparacion' && $receta->preparacion) {
                $costoPrep = $receta->preparacion->costoEnFecha($fecha);
                $subtotal = $cant * $costoPrep;
                $porcentaje = $costoTotal > 0 ? ($subtotal / $costoTotal) * 100 : 0;

                $desglose[] = [
                    'tipo' => 'preparacion',
                    'id' => $receta->preparacion->id,
                    'nombre' => $receta->preparacion->nombre . ' (Masa/Base)',
                    'unidad' => 'porción',
                    'cantidad' => $cant,
                    'precio_unitario' => (float)$costoPrep,
                    'costo_aporte' => (float)$subtotal,
                    'porcentaje' => round($porcentaje, 1),
                ];
            }
        }

        return $desglose;
    }
}
