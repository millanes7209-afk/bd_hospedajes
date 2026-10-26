<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockDiario extends Model
{
    use HasFactory;

    protected $table = 'stock_diario';

    protected $fillable = [
        'fecha',
        'producto_id',
        'cantidad_enviada',
        'despacho_central_id',
        'aceptado',
    ];

    public $timestamps = false;

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
