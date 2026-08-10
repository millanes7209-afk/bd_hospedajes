<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SuperAdmin extends Authenticatable
{
    // Always use central database connection (mysql -> saas_control)
    protected $connection = 'mysql';
    protected $table = 'super_admins';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        '_estado',
    ];

    protected $hidden = [
        'password',
    ];
}
