<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    use HasFactory;

    protected $table = 'promociones';

    protected $fillable = [
        'variante_id',
        'nombre',
        'unidades_por_paquete',
        'precio_paquete',
        'activo',
    ];

    protected $casts = [
        'unidades_por_paquete' => 'integer',
        'precio_paquete' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function variante()
    {
        return $this->belongsTo(VarianteSaltena::class, 'variante_id');
    }
}
