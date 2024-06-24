<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileController;
use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


Route::post('/upload', [FileController::class, 'upload']);
Route::post('/encrypt', [FileController::class, 'encrypt']);
Route::post('/decrypt', [FileController::class, 'decrypt']);
Route::get('/download', function (Request $request) {
    $path = $request->query('path');
    if (Storage::exists($path)) {
        return response()->download(storage_path('app/' . $path));
    } else {
        abort(404, 'File not found.');
    }
});

Route::get('/', function () {
    Debugbar::addmessage('Info!');
    return view('welcome');
});
