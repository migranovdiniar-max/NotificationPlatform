<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function store(Request $request) {
        $event = Event::create([
            'type' => $request->input('type'),
            'payload' => $request->input('payload')]);
        
        return response()->json([
            "id" => $event->id,
            "status" => "created",
        ], 201);
    }
}
