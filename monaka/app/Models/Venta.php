<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'origen',
        'tipo_venta',
        'mesa_id',
        'pedido_id',
        'cliente_nombre',
        'cliente_telefono',
        'direccion_entrega',
        'nota',
        'estado',
        'monto_total',
        'usuario_apertura_id',
        'usuario_cierre_id',
        'fecha_apertura',
        'fecha_cierre'
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function items()
    {
        return $this->hasMany(VentaItem::class, 'venta_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'venta_id');
    }

    public function totalPagado()
    {
        return $this->pagos()->sum('monto');
    }

    public function saldoPendiente()
    {
        return max(0, $this->monto_total - $this->totalPagado());
    }
}
