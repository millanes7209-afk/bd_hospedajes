<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreDiarioPromocionDetalle extends Model
{
    use HasFactory;

    protected $table = 'cierre_diario_promocion_detalle';

    protected $fillable = [
        'cierre_diario_detalle_id',
        'promocion_id',
        'paquetes_vendidos',
    ];

    protected $casts = [
        'paquetes_vendidos' => 'integer',
    ];

    public function detalle()
    {
        return $this->belongsTo(CierreDiarioDetalle::class, 'cierre_diario_detalle_id');
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocion_id');
    }
}
