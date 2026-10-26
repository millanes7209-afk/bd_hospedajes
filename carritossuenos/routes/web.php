<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

// Ruta principal pública para la página de carritos
Route::get('/carritos', function () {
    return view('carritos');
});

// Si se desea que la raíz del dominio redirija a la página de carritos
Route::get('/', function () {
    return redirect('/carritos');
});
