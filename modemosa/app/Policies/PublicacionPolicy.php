<?php

namespace App\Policies;

use App\Models\Publicacion;
use App\Models\User;

class PublicacionPolicy
{
    public function update(User $user, Publicacion $publicacion): bool
    {
        return $user->id === $publicacion->user_id;
    }

    public function delete(User $user, Publicacion $publicacion): bool
    {
        return $user->id === $publicacion->user_id;
    }
}
