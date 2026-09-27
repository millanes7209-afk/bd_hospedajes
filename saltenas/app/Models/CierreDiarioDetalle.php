<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreDiarioDetalle extends Model
{
    use HasFactory;

    protected $table = 'cierre_diario_detalle';

    protected $fillable = [
        'cierre_diario_id',
        'variante_id',
        'cantidad_entregada',
        'cantidad_vendida_normal',
        'cantidad_sobrante',
        'precio_unitario_aplicado',
        'inconsistente',
    ];

    protected $casts = [
        'cantidad_entregada' => 'integer',
        'cantidad_vendida_normal' => 'integer',
        'cantidad_sobrante' => 'integer',
        'precio_unitario_aplicado' => 'decimal:2',
        'inconsistente' => 'boolean',
    ];

    public function cierreDiario()
    {
        return $this->belongsTo(CierreDiario::class, 'cierre_diario_id');
    }

    public function variante()
    {
        return $this->belongsTo(VarianteSaltena::class, 'variante_id');
    }

    public function promocionesDetalle()
    {
        return $this->hasMany(CierreDiarioPromocionDetalle::class, 'cierre_diario_detalle_id');
    }
}
