<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreDiario extends Model
{
    use HasFactory;

    protected $table = 'cierres_diarios';

    protected $fillable = [
        'carrito_id',
        'fecha',
        'temp_min',
        'temp_max',
        'monto_real',
        'monto_estimado',
        'diferencia',
        'dinero_efectivo',
        'dinero_qr',
        'inconsistente',
        'estado',
        'origen',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'temp_min' => 'decimal:2',
        'temp_max' => 'decimal:2',
        'monto_real' => 'decimal:2',
        'monto_estimado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'inconsistente' => 'boolean',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }

    public function detalles()
    {
        return $this->hasMany(CierreDiarioDetalle::class, 'cierre_diario_id');
    }

    public function bovedaMovimiento()
    {
        return $this->hasOne(BovedaMovimiento::class, 'cierre_diario_id');
    }
}
