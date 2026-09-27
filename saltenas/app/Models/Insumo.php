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
    ];

    public function preciosHistorial()
    {
        return $this->hasMany(InsumoPrecioHistorial::class, 'insumo_id')->orderBy('vigente_desde', 'desc');
    }

    public function precioEnFecha($fecha)
    {
        $registro = $this->preciosHistorial()
            ->where('vigente_desde', '<=', $fecha)
            ->orderBy('vigente_desde', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $registro ? $registro->precio : 0.00;
    }
}
