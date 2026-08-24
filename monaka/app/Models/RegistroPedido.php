<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroPedido extends Model
{
    protected $table = 'registros_pedidos';
    protected $primaryKey = 'registroPedidoID';

    const CREATED_AT = 'fecha_creacion';
    public $timestamps = false; // Only has fecha_creacion, no fecha_modificacion

    protected $fillable = ['pedidoID', 'evento', 'detalles'];
}
