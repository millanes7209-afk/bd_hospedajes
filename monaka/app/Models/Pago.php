<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';
    protected $primaryKey = 'pagoID';
    public $timestamps = false;

    protected $fillable = [
        'ventaID',
        'metodo_pago',
        'monto',
        'fecha_creacion'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'ventaID', 'ventaID');
    }
}
