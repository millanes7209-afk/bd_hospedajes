<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreDiario extends Model
{
    use HasFactory;

    protected $table = 'cierres_diarios';

    protected $fillable = [
        'sucursal_id',
        'fecha',
        'clima',
        'temp_min',
        'temp_max',
        'saltenas_vendidas',
        'saltenas_sobrantes',
        'total_efectivo',
        'total_qr',
        'total_recaudado',
        'costo_total_jornada',
        'ganancia_neta',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_efectivo' => 'decimal:2',
        'total_qr' => 'decimal:2',
        'total_recaudado' => 'decimal:2',
        'costo_total_jornada' => 'decimal:2',
        'ganancia_neta' => 'decimal:2',
    ];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
