<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoItem extends Model
{
    use SoftDeletes;

    protected $table = 'pedido_items';

    protected $fillable = ['pedido_id', 'producto_id', 'variante_id', 'nombre_variante', 'cantidad', 'precio_unitario', 'precio_total', 'nota'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'variante_id');
    }
}
