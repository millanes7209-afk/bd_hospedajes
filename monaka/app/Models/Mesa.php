<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';
    protected $primaryKey = 'mesaID';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado',
        'fecha_creacion'
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'mesaID', 'mesaID');
    }

    public function cuentaActiva()
    {
        return $this->hasOne(Venta::class, 'mesaID', 'mesaID')
            ->where('estado', 'abierta');
    }
}
