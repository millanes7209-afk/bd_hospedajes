<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Despacho extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrito_id',
        'variante_id',
        'cantidad',
        'approved',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class);
    }

    public function variante()
    {
        return $this->belongsTo(VariantesSaltena::class, 'variante_id');
    }
}
?>