<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carrito extends Model
{
    use HasFactory;

    protected $table = 'carritos';

    protected $fillable = [
        'nombre',
        'zona',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function cierresDiarios()
    {
        return $this->hasMany(CierreDiario::class, 'carrito_id');
    }
}
