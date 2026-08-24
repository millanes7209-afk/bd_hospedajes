<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaItem extends Model
{
    use HasFactory;

    protected $table = 'venta_items';
    protected $primaryKey = 'ventaItemID';
    public $timestamps = false;

    protected $fillable = [
        'ventaID',
        'productoID',
        'varianteID',
        'nombre_producto',
        'nombre_variante',
        'cantidad',
        'precio_unitario',
        'precio_total',
        'nota',
        'fecha_creacion'
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'ventaID', 'ventaID');
    }
}
