<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarritoTurnoVivo extends Model
{
    use HasFactory;

    protected $table = 'carrito_turnos_vivos';

    protected $fillable = [
        'carrito_id',
        'turno_remoto_id',
        'estado',
        'stock_aceptado',
        'fecha',
        'abierto_at',
        'cerrado_at',
        'total_enviadas',
        'total_vendidas',
        'total_restantes',
        'dinero_total',
        'dinero_efectivo',
        'dinero_qr',
        'desglose_variedades',
        'ultima_actualizacion_pos',
    ];

    protected $casts = [
        'stock_aceptado' => 'boolean',
        'fecha' => 'date',
        'abierto_at' => 'datetime',
        'cerrado_at' => 'datetime',
        'ultima_actualizacion_pos' => 'datetime',
        'total_enviadas' => 'integer',
        'total_vendidas' => 'integer',
        'total_restantes' => 'integer',
        'dinero_total' => 'decimal:2',
        'dinero_efectivo' => 'decimal:2',
        'dinero_qr' => 'decimal:2',
        'desglose_variedades' => 'array',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'carrito_id');
    }
}
