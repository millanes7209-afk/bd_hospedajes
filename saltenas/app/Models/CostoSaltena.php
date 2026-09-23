<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostoSaltena extends Model
{
    use HasFactory;

    protected $table = 'costos_saltenas';

    protected $fillable = [
        'nombre_variante',
        'costo_unidad',
        'precio_venta',
    ];
}
