<?php

namespace App\Http\Controllers;

use App\Models\Interes;
use App\Models\Publicacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InteresController extends Controller
{
    /**
     * Registra el interés del usuario autenticado en una publicación
     * y redirige a WhatsApp con mensaje prellenado.
     */
    public function store(Publicacion $publicacion)
    {
        if (Auth::check()) {
            // Insertar o ignorar (unique constraint)
            Interes::firstOrCreate([
                'user_id' => Auth::id(),
                'publicacion_id' => $publicacion->id,
            ]);
        }

        // Construir link de WhatsApp
        $telefono = ltrim($publicacion->vendedor->telefono_whatsapp ?? '', '+');
        $telefono = '591' . ltrim($telefono, '0');   // Bolivia prefix
        $mensaje = urlencode(
            "Hola, vi tu publicación en Modemosa: *{$publicacion->titulo}* " .
            "(Bs. {$publicacion->precio}) — ¿sigue disponible?"
        );

        return redirect("https://wa.me/{$telefono}?text={$mensaje}");
    }
}
