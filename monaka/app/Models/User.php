<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    public function getKeyName()
    {
        try {
            if (Schema::hasColumn($this->getTable(), 'userID') && !Schema::hasColumn($this->getTable(), 'id')) {
                return 'userID';
            }
        } catch (\Throwable $e) {
        }
        return 'id';
    }

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
        return $this->attributes['rol'] ?? $this->attributes['rolID'] ?? 'ADMINISTRADOR';
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
