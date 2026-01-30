<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\NotificationController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

Route::post('/webhook-test', function (Request $request) {
    Log::info('Webhook received', [
        'headers' => $request->headers->all(),
        'body' => $request->all(),
        'raw' => $request->getContent(),
    ]);

    return response()->json(['ok' => true]);
});

Route::post('/events', [EventController::class, 'store']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

Route::post('/notifications/{notification}/retry', [NotificationController::class, 'retry']);
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/{notification}', [NotificationController::class, 'show']);

Route::get('/ping', function () {
    return response()->json(['ok' => true]);
});