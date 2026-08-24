<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVariante extends Model
{
    protected $table = 'producto_variantes';
    protected $primaryKey = 'varianteID';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    protected $fillable = ['productoID', 'nombre_variante', 'precio', 'precio_promo', 'dias_promo', 'activo', 'imagen'];
}
