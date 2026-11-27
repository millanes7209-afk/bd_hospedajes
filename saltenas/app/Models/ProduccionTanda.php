<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionTanda extends Model
{
    use HasFactory;

    protected $table = 'produccion_tandas';

    protected $fillable = [
        'fecha',
        'preparacion_id',
        'tandas',
        'cantidad_producida',
        'costo_total',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'tandas' => 'decimal:2',
        'cantidad_producida' => 'decimal:3',
        'costo_total' => 'decimal:2',
    ];

    public function preparacion()
    {
        return $this->belongsTo(Preparacion::class, 'preparacion_id');
    }
}
