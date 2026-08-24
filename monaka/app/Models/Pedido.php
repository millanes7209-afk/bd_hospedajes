<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';
    protected $primaryKey = 'pedidoID';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    protected $fillable = ['numero_pedido', 'cliente_nombre', 'cliente_telefono', 'tipo_pedido', 'numero_mesa', 'direccion_entrega', 'nota', 'estado', 'estado_pago', 'metodo_pago', 'monto_total', 'aceptado_en', 'impreso_en'];
}
