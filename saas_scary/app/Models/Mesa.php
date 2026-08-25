<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mesas';

    protected $fillable = [
        'nombre',
        'estado',
        'user_id'
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'mesa_id');
    }

    public function cuentaActiva()
    {
        return $this->hasOne(Venta::class, 'mesa_id')
            ->where('estado', 'abierta');
    }
}
