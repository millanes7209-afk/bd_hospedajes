<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'precio',
        'activo',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function stockDiario()
    {
        return $this->hasMany(StockDiario::class);
    }
}
