<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = ['numero_pedido', 'cliente_nombre', 'cliente_telefono', 'tipo_pedido', 'numero_mesa', 'direccion_entrega', 'nota', 'estado', 'estado_pago', 'metodo_pago', 'monto_total', 'aceptado_en', 'impreso_en'];
}
