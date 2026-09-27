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
    ];

    protected $casts = [
        'rinde_cantidad' => 'decimal:2',
    ];

    public function receta()
    {
        return $this->hasMany(PreparacionReceta::class, 'preparacion_id');
    }

    public function costoEnFecha($fecha)
    {
        $costoTotalLote = 0;

        foreach ($this->receta as $item) {
            $precioInsumo = $item->insumo ? $item->insumo->precioEnFecha($fecha) : 0;
            $costoTotalLote += ($item->cantidad_usada * $precioInsumo);
        }

        if ($this->rinde_cantidad > 0) {
            return $costoTotalLote / $this->rinde_cantidad;
        }

        return 0;
    }
}
