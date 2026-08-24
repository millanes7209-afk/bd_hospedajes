<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SuperAdmin extends Authenticatable
{
    use HasFactory;

    protected $connection = 'saas_control';
    protected $table = 'super_admins';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        '_estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
