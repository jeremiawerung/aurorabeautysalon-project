<?php

use App\Http\Controllers\PembayaranController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/notification', [PembayaranController::class, 'notificationHandler']);

Route::get('/abc', function () {
    return 'abc';
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
