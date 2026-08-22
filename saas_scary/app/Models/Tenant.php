<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $connection = 'saas_control';
    protected $table = 'tenants';

    protected $fillable = [
        'nombre',
        'subdominio',
        'rubro',
        'db_host',
        'db_nombre',
        'db_usuario',
        'db_password',
        'logo',
        'eslogan',
        'primary_color',
        'accent_color',
        'dark_bg_color',
        'dark_card_color',
        'light_bg_color',
        'light_card_color',
        '_estado',
    ];
}
