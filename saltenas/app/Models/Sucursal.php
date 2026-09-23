<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'direccion',
        'encargado',
        'activa',
    ];

    public function cierres()
    {
        return $this->hasMany(CierreDiario::class);
    }
}
