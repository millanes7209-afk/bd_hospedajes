<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'productoID';

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_modificacion';

    protected $fillable = [
        'categoriaID',
        'nombre',
        'slug',
        'descripcion',
        'tipo',
        'precio',
        'precio_promo',
        'dia_promo',
        'stock',
        'activo',
        'disponible',
        'disponible_desde',
        'imagen',
        'orden_mostrado'
    ];
}
