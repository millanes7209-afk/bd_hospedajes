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

    public function costoEnFecha($fecha)
    {
        $costoTotalUnitario = 0;

        foreach ($this->recetas as $receta) {
            if ($receta->tipo_componente === 'insumo' && $receta->insumo) {
                $precioInsumo = $receta->insumo->precioEnFecha($fecha);
                $costoTotalUnitario += ($receta->cantidad_usada * $precioInsumo);
            } elseif ($receta->tipo_componente === 'preparacion' && $receta->preparacion) {
                $costoPreparacionUnidad = $receta->preparacion->costoEnFecha($fecha);
                $costoTotalUnitario += ($receta->cantidad_usada * $costoPreparacionUnidad);
            }
        }

        return $costoTotalUnitario;
    }
}
