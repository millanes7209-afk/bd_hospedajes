<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DespachoDetalle extends Model
{
    use HasFactory;

    protected $table = 'despacho_detalles';

    protected $fillable = [
        'despacho_diario_id',
        'variante_id',
        'cantidad_enviada',
        'cantidad_aceptada',
    ];

    public function despachoDiario()
    {
        return $this->belongsTo(DespachoDiario::class);
    }

    public function variante()
    {
        return $this->belongsTo(VarianteSaltena::class);
    }
}
