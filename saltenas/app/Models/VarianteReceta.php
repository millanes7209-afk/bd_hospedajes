<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VarianteReceta extends Model
{
    use HasFactory;

    protected $table = 'variante_receta';

    protected $fillable = [
        'variante_id',
        'tipo_componente',
        'insumo_id',
        'preparacion_id',
        'cantidad_usada',
    ];

    protected $casts = [
        'cantidad_usada' => 'decimal:4',
    ];

    public function variante()
    {
        return $this->belongsTo(VarianteSaltena::class, 'variante_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }

    public function preparacion()
    {
        return $this->belongsTo(Preparacion::class, 'preparacion_id');
    }
}
