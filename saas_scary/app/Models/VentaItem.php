<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentaItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'venta_items';

    protected $fillable = [
        'venta_id',
        'producto_id',
        'variante_id',
        'nombre_producto',
        'nombre_variante',
        'cantidad',
        'precio_unitario',
        'precio_total',
        'nota'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
