<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductoVariante extends Model
{
    use SoftDeletes;

    protected $table = 'producto_variantes';

    protected $fillable = ['producto_id', 'nombre_variante', 'precio', 'precio_promo', 'dias_promo', 'activo', 'imagen', 'user_id'];
}
