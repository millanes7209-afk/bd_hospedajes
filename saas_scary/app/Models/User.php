<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $guarded = [];

    public function getNameAttribute()
    {
        return $this->attributes['name'] ?? $this->attributes['nombre'] ?? '';
    }

    public function getNombreAttribute()
    {
        return $this->attributes['nombre'] ?? $this->attributes['name'] ?? '';
    }

    public function getRolAttribute()
    {
        return $this->attributes['rol'] ?? 'ADMINISTRADOR';
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
