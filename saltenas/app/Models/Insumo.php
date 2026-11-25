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
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    public function preciosHistorial()
    {
        return $this->hasMany(InsumoPrecioHistorial::class, 'insumo_id')
            ->orderBy('vigente_desde', 'desc')
            ->orderBy('id', 'desc');
    }

    public function precioEnFecha($fecha)
    {
        $registro = $this->preciosHistorial()
            ->where('vigente_desde', '<=', $fecha)
            ->orderBy('vigente_desde', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $registro ? (float)$registro->precio : 0.00;
    }

    public function getUltimoPrecioAttribute(): float
    {
        $ultimo = $this->preciosHistorial()->first();
        return $ultimo ? (float)$ultimo->precio : 0.00;
    }
}
