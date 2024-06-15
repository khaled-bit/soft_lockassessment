<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;


Route::post('/upload', [FileController::class, 'upload']);
Route::post('/encrypt', [FileController::class, 'encrypt']);
Route::post('/decrypt', [FileController::class, 'decrypt']);

Route::get('/', function () {
    return view('welcome');
});
