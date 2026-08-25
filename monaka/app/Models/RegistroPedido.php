<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPedido extends Model
{
    protected $table = 'registros_pedidos';
    protected $primaryKey = 'registroPedidoID';

    const CREATED_AT = 'fecha_creacion'; // (Will be refactored to 'created_at' during execution)
    const UPDATED_AT = null;

    protected $fillable = ['pedido_id', 'evento', 'detalles'];
}
