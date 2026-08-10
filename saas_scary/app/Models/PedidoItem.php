<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedidoItem extends Model
{
    protected $table = 'pedido_items';
    protected $primaryKey = 'pedidoItemID';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    protected $fillable = ['pedidoID', 'productoID', 'nombre_variante', 'cantidad', 'precio_unitario', 'precio_total', 'nota'];
}
