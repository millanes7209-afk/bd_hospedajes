<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsumoPrecioHistorial extends Model
{
    use HasFactory;

    protected $table = 'insumo_precios_historial';

    public $timestamps = false;

    protected $fillable = [
        'insumo_id',
        'precio',
        'vigente_desde',
        'created_at',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'vigente_desde' => 'date',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
