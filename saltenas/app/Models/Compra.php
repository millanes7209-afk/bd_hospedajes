<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'fecha',
        'monto_total',
        'metodo_pago',
        'proveedor',
        'comprobante_nro',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto_total' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class, 'compra_id');
    }

    public function bovedaMovimiento()
    {
        return $this->hasOne(BovedaMovimiento::class, 'compra_id');
    }
}
