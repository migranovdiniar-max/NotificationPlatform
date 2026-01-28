<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;

Route::post('/events', [EventController::class, 'store']);

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});