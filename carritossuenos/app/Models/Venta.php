<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'producto_id',
        'promocion_id',
        'cantidad',
        'monto',
        'tipo_pago',
        'fecha_hora',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class);
    }
}
