<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionArmado extends Model
{
    use HasFactory;

    protected $table = 'produccion_armados';

    protected $fillable = [
        'fecha',
        'variante_id',
        'cantidad_armada',
        'tapa_preparacion_id',
        'tapas_usadas',
        'jigote_preparacion_id',
        'jigote_porciones_usadas',
        'costo_total_armado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cantidad_armada' => 'decimal:2',
        'tapas_usadas' => 'decimal:2',
        'jigote_porciones_usadas' => 'decimal:2',
        'costo_total_armado' => 'decimal:2',
    ];

    public function variante()
    {
        return $this->belongsTo(VarianteSaltena::class, 'variante_id');
    }

    public function tapaPreparacion()
    {
        return $this->belongsTo(Preparacion::class, 'tapa_preparacion_id');
    }

    public function jigotePreparacion()
    {
        return $this->belongsTo(Preparacion::class, 'jigote_preparacion_id');
    }
}
