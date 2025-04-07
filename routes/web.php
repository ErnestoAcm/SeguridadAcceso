<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::view('/login', 'login')->name('login');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::post('/registrar-usuario', [AuthController::class, 'registrarUsuario'])->name('registrarUsuario');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/', function () {
    return view('welcome');
});
