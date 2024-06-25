<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use Barryvdh\Debugbar\Facades\Debugbar;
use App\Http\Controllers\UploadController;


Route::post('/upload', [FileController::class, 'upload'])->name('upload.file');;
Route::post('/encrypt', [FileController::class, 'encrypt']);
Route::post('/decrypt', [FileController::class, 'decrypt']);
// web.php
Route::post('/upload_chunk', [UploadController::class, 'handleChunk'])->name('upload.chunk');
Route::get('/', function () {
    Debugbar::addmessage('Info!');
    return view('welcome');
});
