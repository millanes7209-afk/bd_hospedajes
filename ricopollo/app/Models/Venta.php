<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';
    protected $primaryKey = 'ventaID';
    public $timestamps = false;

    protected $fillable = [
        'origen',
        'tipo_venta',
        'mesaID',
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
        return $this->belongsTo(Mesa::class, 'mesaID', 'mesaID');
    }

    public function items()
    {
        return $this->hasMany(VentaItem::class, 'ventaID', 'ventaID');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'ventaID', 'ventaID');
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
