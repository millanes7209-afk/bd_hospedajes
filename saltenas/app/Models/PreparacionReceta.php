<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreparacionReceta extends Model
{
    use HasFactory;

    protected $table = 'preparacion_receta';

    protected $fillable = [
        'preparacion_id',
        'insumo_id',
        'cantidad_usada',
    ];

    protected $casts = [
        'cantidad_usada' => 'decimal:4',
    ];

    public function preparacion()
    {
        return $this->belongsTo(Preparacion::class, 'preparacion_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
