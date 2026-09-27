<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'slug', 'icono', 'activa'];

    public function publicaciones(): HasMany
    {
        return $this->hasMany(Publicacion::class);
    }
}
