<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use Barryvdh\Debugbar\Facades\Debugbar;


Route::post('/upload', [FileController::class, 'upload']);
Route::post('/encrypt', [FileController::class, 'encrypt']);
Route::post('/decrypt', [FileController::class, 'decrypt']);

Route::get('/', function () {
    Debugbar::addmessage('Info!');
    return view('welcome');
});
