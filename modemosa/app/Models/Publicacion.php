<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publicacion extends Model
{
    protected $fillable = [
        'user_id',
        'categoria_id',
        'titulo',
        'descripcion',
        'precio',
        'talla',
        'marca',
        'estado_prenda',
        'estado_pub',
        'color_dominante',
        'temporada',
        'tags_estilo',
        'comprador_id',
    ];

    protected $casts = [
        'tags_estilo' => 'array',
        'precio' => 'decimal:2',
    ];

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function comprador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'comprador_id');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(PublicacionFoto::class)->orderBy('orden');
    }

    public function fotoPrincipal(): HasMany
    {
        return $this->hasMany(PublicacionFoto::class)->where('es_principal', true)->limit(1);
    }

    public function intereses(): HasMany
    {
        return $this->hasMany(Interes::class);
    }

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class);
    }

    /** URL de la primera foto o un placeholder */
    public function getFotoUrlAttribute(): string
    {
        $foto = $this->fotos->first();
        return $foto ? asset('storage/' . $foto->ruta) : asset('img/placeholder.png');
    }
}
