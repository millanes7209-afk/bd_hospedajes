<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

// Nueva pantalla de abrir turno
Route::get('/', function () {
    return view('abrir_turno');
});
