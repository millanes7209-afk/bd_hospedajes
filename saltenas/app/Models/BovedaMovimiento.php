<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BovedaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'boveda_movimientos';

    public $timestamps = false;

    protected $fillable = [
        'tipo',
        'monto',
        'dinero_efectivo',
        'dinero_qr',
        'fecha',
        'cierre_diario_id',
        'compra_id',
        'created_at',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'date',
    ];

    public function cierreDiario()
    {
        return $this->belongsTo(CierreDiario::class, 'cierre_diario_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }
}
