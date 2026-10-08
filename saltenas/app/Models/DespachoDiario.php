<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespachoDiario extends Model
{
    use HasFactory;

    protected $table = 'despachos_diarios';

    protected $fillable = [
        'carrito_id',
        'fecha',
        'estado',
        'user_id',
        'observaciones',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(DespachoDetalle::class);
    }
}
